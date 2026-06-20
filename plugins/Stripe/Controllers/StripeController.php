<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Stripe\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Repositories\Order\PaymentRepo;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\StateMachineService;
use Plugin\Stripe\Services\StripeService;

class StripeController extends Controller
{
    /**
     * 订单支付扣款
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function capture(Request $request): JsonResponse
    {
        try {
            $creditCardData = $request->all();

            $filters = [
                'number'      => $request->get('order_number'),
                'customer_id' => current_customer_id(),
            ];

            $order = OrderRepo::getInstance()->builder($filters)->first();

            $paymentData = ['amount' => $order->total, 'paid' => false, 'reference' => $creditCardData];
            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, $paymentData);

            $result = (new StripeService($order))->capture($creditCardData);
            $isPaid = $result['paid'] && $result['captured'];

            $paymentData = ['charge_id' => $result->id, 'amount' => $order->total, 'paid' => $isPaid, 'reference' => $result];
            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, $paymentData);

            if ($isPaid) {
                StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

                return json_success(trans('Stripe::common.capture_success'));
            }

            return json_success(trans('Stripe::common.capture_fail'));

        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Stripe events that confirm a successful payment.
     */
    private const PAID_EVENT_TYPES = [
        'charge.succeeded',
        'payment_intent.succeeded',
        'checkout.session.completed',
    ];

    /**
     * Webhook from stripe
     * https://dashboard.stripe.com/webhooks
     *
     * 安全要求：必须使用 Webhook Secret 校验 Stripe-Signature 签名，
     * 否则任何人都可伪造 charge.succeeded 报文将订单置为已支付（资损风险）。
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function callback(Request $request): JsonResponse
    {
        $payload       = $request->getContent();
        $signature     = $request->header('Stripe-Signature');
        $webhookSecret = (string) plugin_setting('stripe', 'webhook_secret');

        // 未配置 Webhook Secret 时拒绝处理（fail closed），避免无验签的伪造回调。
        if ($webhookSecret === '') {
            Log::channel('payment')->error('stripe.webhook.secret_missing', [
                'message' => 'Stripe webhook_secret is not configured; rejecting webhook.',
            ]);

            if (app()->bound('sentry')) {
                \Sentry\captureMessage('Stripe webhook received but webhook_secret is not configured');
            }

            return json_fail('Webhook secret not configured', null, 400);
        }

        // 校验签名，验证失败一律拒绝。
        try {
            $event = \Stripe\Webhook::constructEvent($payload, (string) $signature, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::channel('payment')->warning('stripe.webhook.invalid_payload', ['error' => $e->getMessage()]);

            return json_fail('Invalid payload', null, 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::channel('payment')->warning('stripe.webhook.signature_failed', ['error' => $e->getMessage()]);

            if (app()->bound('sentry')) {
                \Sentry\captureMessage('Stripe webhook signature verification failed');
            }

            return json_fail('Invalid signature', null, 400);
        }

        $type        = $event->type;
        $object      = $event->data->object ?? null;
        $orderNumber = $this->extractOrderNumber($object);

        Log::channel('payment')->info('stripe.webhook.received', [
            'event_id'     => $event->id,
            'type'         => $type,
            'order_number' => $orderNumber,
        ]);

        // Sentry 上下文
        if (app()->bound('sentry')) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($type, $orderNumber, $event) {
                $scope->setContext('stripe_webhook', [
                    'event_id'     => $event->id,
                    'type'         => $type,
                    'order_number' => $orderNumber,
                ]);
                $scope->setTag('payment.provider', 'stripe');
                $scope->setTag('webhook.type', $type);
            });
        }

        // 只处理表示支付成功的事件类型，其它事件直接 200 确认（避免 Stripe 重试）。
        if (! in_array($type, self::PAID_EVENT_TYPES, true)) {
            return json_success('ignored');
        }

        try {
            $order = $orderNumber ? OrderRepo::getInstance()->getOrderByNumber($orderNumber) : null;

            if (! $order) {
                Log::channel('payment')->warning('stripe.webhook.order_not_found', [
                    'event_id'     => $event->id,
                    'order_number' => $orderNumber,
                ]);

                // 200：订单不存在时无需 Stripe 重试。
                return json_success('order not found');
            }

            // 幂等：订单已处于已支付及之后的状态时，直接确认，不重复流转。
            if (in_array($order->status, StateMachineService::getValidStatuses(), true)) {
                Log::channel('payment')->info('stripe.webhook.already_paid', [
                    'event_id'     => $event->id,
                    'order_number' => $orderNumber,
                    'status'       => $order->status,
                ]);

                return json_success('already processed');
            }

            // 金额交叉校验（防御性）：签名已保证报文来自 Stripe，此处仅记录异常以便排查配置问题。
            $this->verifyAmount($event, $order, $orderNumber);

            StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

            Log::channel('payment')->info('stripe.webhook.success', [
                'event_id'     => $event->id,
                'type'         => $type,
                'order_number' => $orderNumber,
            ]);

            return json_success(trans('Stripe::common.capture_success'));

        } catch (\Exception $e) {
            Log::channel('payment')->error('stripe.webhook.failed', [
                'event_id'     => $event->id,
                'type'         => $type,
                'order_number' => $orderNumber,
                'error'        => $e->getMessage(),
            ]);

            if (app()->bound('sentry')) {
                \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($e, $type, $orderNumber) {
                    $scope->setLevel(\Sentry\Severity::fatal());
                    $scope->setContext('payment_failure', [
                        'provider'     => 'stripe',
                        'type'         => $type,
                        'order_number' => $orderNumber,
                        'error'        => $e->getMessage(),
                    ]);
                    \Sentry\captureException($e);
                });
            }

            // 500：处理失败让 Stripe 稍后重试。
            return json_fail($e->getMessage(), null, 500);
        }
    }

    /**
     * Extract the order number from a Stripe event object (charge / payment_intent / checkout.session).
     *
     * @param  mixed  $object
     * @return string
     */
    private function extractOrderNumber(mixed $object): string
    {
        if (! $object) {
            return '';
        }

        $metadata = $object->metadata ?? null;
        if ($metadata && isset($metadata->order_number) && $metadata->order_number !== '') {
            return (string) $metadata->order_number;
        }

        // checkout.session 在 client_reference_id 中也带有订单号
        if (isset($object->client_reference_id) && $object->client_reference_id) {
            return (string) $object->client_reference_id;
        }

        return '';
    }

    /**
     * Defensive amount cross-check. The signature already guarantees authenticity,
     * so a mismatch here usually means a configuration/currency issue worth logging.
     *
     * @param  \Stripe\Event  $event
     * @param  mixed  $order
     * @param  string  $orderNumber
     * @return void
     */
    private function verifyAmount(\Stripe\Event $event, $order, string $orderNumber): void
    {
        $object = $event->data->object ?? null;
        if (! $object) {
            return;
        }

        // 不同事件金额字段不同：charge/payment_intent => amount；checkout.session => amount_total
        $paidMinorUnit = $object->amount ?? ($object->amount_total ?? null);
        if ($paidMinorUnit === null) {
            return;
        }

        $expectedMinorUnit = (int) round(((float) $order->total) * 100);
        if (abs((int) $paidMinorUnit - $expectedMinorUnit) > 1) {
            Log::channel('payment')->warning('stripe.webhook.amount_mismatch', [
                'event_id'     => $event->id,
                'order_number' => $orderNumber,
                'paid_amount'  => $paidMinorUnit,
                'expected'     => $expectedMinorUnit,
                'order_total'  => $order->total,
            ]);

            if (app()->bound('sentry')) {
                \Sentry\captureMessage("Stripe webhook amount mismatch for order {$orderNumber}");
            }
        }
    }

    /**
     * 创建 Stripe Checkout Session
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function createCheckoutSession(Request $request): JsonResponse
    {
        try {
            $filters = [
                'number'      => $request->get('order_number'),
                'customer_id' => current_customer_id(),
            ];

            $order = OrderRepo::getInstance()->builder($filters)->first();

            if (! $order) {
                return json_fail(trans('Stripe::common.order_not_found'));
            }

            $stripeService = new StripeService($order);
            $session       = $stripeService->createCheckoutSession([
                'success_url' => front_route('payment.success', ['order_number' => $order->number]),
                'cancel_url'  => front_route('payment.cancel', ['order_number' => $order->number]),
                'metadata'    => [
                    'order_number' => $order->number,
                    'customer_id'  => $order->customer_id,
                ],
            ]);

            return read_json_success([
                'session_id'   => $session->id,
                'checkout_url' => $session->url,
            ]);

        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function checkoutSuccess(Request $request): JsonResponse
    {
        return json_success(trans('Stripe::common.checkout_success'));
    }

    public function checkoutCancel(Request $request): JsonResponse
    {
        return json_success(trans('Stripe::common.checkout_cancel'));
    }
}
