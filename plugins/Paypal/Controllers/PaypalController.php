<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Paypal\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Repositories\Order\PaymentRepo;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\StateMachineService;
use Plugin\Paypal\Services\PaypalService;

class PaypalController extends Controller
{
    /**
     * PayPal Webhook 中表示「支付完成」的事件类型。
     */
    private const PAID_EVENT_TYPES = [
        'PAYMENT.CAPTURE.COMPLETED',
        'CHECKOUT.ORDER.COMPLETED',
    ];

    /**
     * 创建 PayPal 订单并返回买家审批跳转链接。
     *
     * 前端拿到 approve_url 后将浏览器重定向至 PayPal 完成授权。
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function createOrder(Request $request): JsonResponse
    {
        try {
            $filters = [
                'number'      => $request->get('order_number'),
                'customer_id' => current_customer_id(),
            ];

            $order = OrderRepo::getInstance()->builder($filters)->first();

            if (! $order) {
                return json_fail(trans('Paypal::common.order_not_found'));
            }

            $service  = new PaypalService($order);
            $response = $service->createPaypalOrder(
                front_route('paypal_return', ['order_number' => $order->number]),
                front_route('paypal_cancel', ['order_number' => $order->number]),
            );

            $paypalOrderId = $response['id'] ?? '';
            $approveUrl    = PaypalService::extractApproveUrl($response);

            if ($paypalOrderId === '' || $approveUrl === '') {
                Log::channel('payment')->error('paypal.create_order.invalid_response', [
                    'order_number' => $order->number,
                    'response'     => $response,
                ]);

                return json_fail(trans('Paypal::common.create_order_fail'));
            }

            // 预创建一条未支付的 payment 记录，写入 PayPal 订单号作为流水凭证。
            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
                'charge_id' => $paypalOrderId,
                'amount'    => $order->total,
                'paid'      => false,
                'reference' => $response,
            ]);

            return read_json_success([
                'paypal_order_id' => $paypalOrderId,
                'approve_url'     => $approveUrl,
            ]);

        } catch (\Throwable $e) {
            Log::channel('payment')->error('paypal.create_order.failed', [
                'order_number' => $request->get('order_number'),
                'error'        => $e->getMessage(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * 买家在 PayPal 授权成功后的回跳处理：服务端捕获扣款。
     *
     * PayPal 回跳会携带 token（即 PayPal 订单号）与商城 order_number。
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function paypalReturn(Request $request): RedirectResponse
    {
        $orderNumber   = (string) $request->get('order_number');
        $paypalOrderId = (string) $request->get('token');

        try {
            $order = $orderNumber ? OrderRepo::getInstance()->getOrderByNumber($orderNumber) : null;

            if (! $order || $paypalOrderId === '') {
                return redirect()->to(front_route('payment.fail', ['order_number' => $orderNumber]));
            }

            // 幂等：订单已支付（及之后状态）直接跳成功页，不重复捕获。
            if (in_array($order->status, StateMachineService::getValidStatuses(), true)) {
                return redirect()->to(front_route('payment.success', ['order_number' => $orderNumber]));
            }

            $service = new PaypalService($order);
            $result  = $service->captureOrder($paypalOrderId);

            $status = $result['status'] ?? '';
            $isPaid = $status === 'COMPLETED';

            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
                'charge_id' => $paypalOrderId,
                'amount'    => $order->total,
                'paid'      => $isPaid,
                'reference' => $result,
            ]);

            if ($isPaid) {
                StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                Log::channel('payment')->info('paypal.capture.success', [
                    'order_number'    => $orderNumber,
                    'paypal_order_id' => $paypalOrderId,
                ]);

                return redirect()->to(front_route('payment.success', ['order_number' => $orderNumber]));
            }

            Log::channel('payment')->warning('paypal.capture.not_completed', [
                'order_number'    => $orderNumber,
                'paypal_order_id' => $paypalOrderId,
                'status'          => $status,
            ]);

            return redirect()->to(front_route('payment.fail', ['order_number' => $orderNumber]));

        } catch (\Throwable $e) {
            Log::channel('payment')->error('paypal.capture.failed', [
                'order_number'    => $orderNumber,
                'paypal_order_id' => $paypalOrderId,
                'error'           => $e->getMessage(),
            ]);

            if (app()->bound('sentry')) {
                \Sentry\captureException($e);
            }

            return redirect()->to(front_route('payment.fail', ['order_number' => $orderNumber]));
        }
    }

    /**
     * 买家在 PayPal 取消支付后的回跳处理。
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function paypalCancel(Request $request): RedirectResponse
    {
        $orderNumber = (string) $request->get('order_number');

        return redirect()->to(front_route('payment.cancel', ['order_number' => $orderNumber]));
    }

    /**
     * PayPal Webhook 回调（异步兜底确认支付）。
     *
     * 安全要求：必须配置 Webhook ID 并校验 PayPal 传输签名，
     * 否则任何人都可伪造 PAYMENT.CAPTURE.COMPLETED 报文将订单置为已支付（资损风险）。
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function callback(Request $request): JsonResponse
    {
        $webhookId = (string) plugin_setting('paypal.webhook_id');

        // 未配置 Webhook ID 时拒绝处理（fail closed），避免无验签的伪造回调。
        if ($webhookId === '') {
            Log::channel('payment')->error('paypal.webhook.webhook_id_missing', [
                'message' => 'PayPal webhook_id is not configured; rejecting webhook.',
            ]);

            if (app()->bound('sentry')) {
                \Sentry\captureMessage('PayPal webhook received but webhook_id is not configured');
            }

            return json_fail('Webhook id not configured', null, 400);
        }

        $event       = json_decode($request->getContent(), true) ?: [];
        $type        = (string) ($event['event_type'] ?? '');
        $orderNumber = $this->extractOrderNumber($event);

        // 调用 PayPal 验签接口校验回调真实性。
        try {
            $client       = PaypalService::makeClient();
            $verification = $client->verifyWebHook([
                'auth_algo'         => $request->header('PAYPAL-AUTH-ALGO'),
                'cert_url'          => $request->header('PAYPAL-CERT-URL'),
                'transmission_id'   => $request->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig'  => $request->header('PAYPAL-TRANSMISSION-SIG'),
                'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id'        => $webhookId,
                'webhook_event'     => $event,
            ]);

            if (($verification['verification_status'] ?? '') !== 'SUCCESS') {
                Log::channel('payment')->warning('paypal.webhook.signature_failed', [
                    'type'         => $type,
                    'order_number' => $orderNumber,
                    'verification' => $verification,
                ]);

                if (app()->bound('sentry')) {
                    \Sentry\captureMessage('PayPal webhook signature verification failed');
                }

                return json_fail('Invalid signature', null, 400);
            }
        } catch (\Throwable $e) {
            Log::channel('payment')->error('paypal.webhook.verify_error', [
                'type'         => $type,
                'order_number' => $orderNumber,
                'error'        => $e->getMessage(),
            ]);

            return json_fail('Webhook verification error', null, 400);
        }

        Log::channel('payment')->info('paypal.webhook.received', [
            'event_id'     => $event['id'] ?? '',
            'type'         => $type,
            'order_number' => $orderNumber,
        ]);

        // 仅处理表示支付完成的事件，其它事件直接 200 确认（避免 PayPal 重试）。
        if (! in_array($type, self::PAID_EVENT_TYPES, true)) {
            return json_success('ignored');
        }

        try {
            $order = $orderNumber ? OrderRepo::getInstance()->getOrderByNumber($orderNumber) : null;

            if (! $order) {
                Log::channel('payment')->warning('paypal.webhook.order_not_found', [
                    'order_number' => $orderNumber,
                ]);

                return json_success('order not found');
            }

            // 幂等：订单已支付（及之后状态）直接确认，不重复流转。
            if (in_array($order->status, StateMachineService::getValidStatuses(), true)) {
                return json_success('already processed');
            }

            $this->verifyAmount($event, $order, $orderNumber);

            StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

            Log::channel('payment')->info('paypal.webhook.success', [
                'type'         => $type,
                'order_number' => $orderNumber,
            ]);

            return json_success(trans('Paypal::common.capture_success'));

        } catch (\Throwable $e) {
            Log::channel('payment')->error('paypal.webhook.failed', [
                'type'         => $type,
                'order_number' => $orderNumber,
                'error'        => $e->getMessage(),
            ]);

            if (app()->bound('sentry')) {
                \Sentry\captureException($e);
            }

            // 500：处理失败让 PayPal 稍后重试。
            return json_fail($e->getMessage(), null, 500);
        }
    }

    /**
     * 从 Webhook 事件体中提取商城订单号。
     *
     * PayPal 在 purchase_units[].custom_id / invoice_id 中携带订单号。
     *
     * @param  array  $event
     * @return string
     */
    private function extractOrderNumber(array $event): string
    {
        $resource = $event['resource'] ?? [];

        // PAYMENT.CAPTURE.COMPLETED：custom_id / invoice_id 直接在 resource 上
        if (! empty($resource['custom_id'])) {
            return (string) $resource['custom_id'];
        }
        if (! empty($resource['invoice_id'])) {
            return (string) $resource['invoice_id'];
        }

        // CHECKOUT.ORDER.*：订单号在 purchase_units 内
        $purchaseUnits = $resource['purchase_units'] ?? [];
        foreach ($purchaseUnits as $unit) {
            if (! empty($unit['custom_id'])) {
                return (string) $unit['custom_id'];
            }
            if (! empty($unit['invoice_id'])) {
                return (string) $unit['invoice_id'];
            }
        }

        return '';
    }

    /**
     * 金额交叉校验（防御性）：验签已保证报文来自 PayPal，此处仅记录异常以便排查配置问题。
     *
     * @param  array  $event
     * @param  mixed  $order
     * @param  string  $orderNumber
     * @return void
     */
    private function verifyAmount(array $event, $order, string $orderNumber): void
    {
        $resource = $event['resource'] ?? [];

        // PAYMENT.CAPTURE.COMPLETED：金额在 resource.amount.value
        $paidValue = $resource['amount']['value']
            ?? ($resource['purchase_units'][0]['amount']['value'] ?? null);

        if ($paidValue === null) {
            return;
        }

        $service        = new PaypalService($order);
        $expected       = $service->buildAmount();
        $expectedValue  = (float) $expected['value'];
        $paidValueFloat = (float) $paidValue;

        if (abs($paidValueFloat - $expectedValue) > 0.01) {
            Log::channel('payment')->warning('paypal.webhook.amount_mismatch', [
                'order_number' => $orderNumber,
                'paid_amount'  => $paidValueFloat,
                'expected'     => $expectedValue,
                'order_total'  => $order->total,
            ]);

            if (app()->bound('sentry')) {
                \Sentry\captureMessage("PayPal webhook amount mismatch for order {$orderNumber}");
            }
        }
    }
}
