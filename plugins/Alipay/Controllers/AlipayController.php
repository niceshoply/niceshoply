<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Alipay\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Repositories\Order\PaymentRepo;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\StateMachineService;
use Plugin\Alipay\Services\AlipayService;

class AlipayController extends Controller
{
    /**
     * 手机 WAP 支付页：输出自动提交表单 HTML，供 App WebView 加载。
     */
    public function wap(Request $request, string $number)
    {
        try {
            $order = Order::query()->where('number', $number)->firstOrFail();

            if ($order->status !== 'unpaid') {
                return redirect(front_route('payment.success'));
            }

            if (($order->billing_method_code ?? '') !== 'alipay') {
                abort(404);
            }

            $html = (new AlipayService($order))->wap();

            return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
        } catch (\Throwable $e) {
            Log::channel('payment')->error('alipay.wap.failed', [
                'order_number' => $number,
                'error'        => $e->getMessage(),
            ]);

            return redirect(front_route('orders.pay', ['number' => $number]))
                ->with('error', $e->getMessage());
        }
    }

    /**
     * 支付宝异步回调通知。
     */
    public function notify()
    {
        try {
            $service = AlipayService::getInstance();
            $payload = $service->verifyCallback();

            $orderNumber = $payload['out_trade_no'] ?? '';
            $tradeStatus = $payload['trade_status'] ?? '';

            if ($orderNumber === '' || ! in_array($tradeStatus, ['TRADE_SUCCESS', 'TRADE_FINISHED'], true)) {
                return $service->callbackSuccessResponse();
            }

            $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);
            if (! $order) {
                Log::channel('payment')->warning('alipay.notify.order_not_found', ['order_number' => $orderNumber]);

                return $service->callbackSuccessResponse();
            }

            if (in_array($order->status, StateMachineService::getValidStatuses(), true)) {
                return $service->callbackSuccessResponse();
            }

            $paidAmount    = (float) ($payload['total_amount'] ?? 0);
            $expectedAmount = (float) $order->total;
            if ($paidAmount > 0 && abs($paidAmount - $expectedAmount) > 0.01) {
                Log::channel('payment')->warning('alipay.notify.amount_mismatch', [
                    'order_number' => $orderNumber, 'paid' => $paidAmount, 'expected' => $expectedAmount,
                ]);

                return $service->callbackSuccessResponse();
            }

            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
                'charge_id' => $payload['trade_no'] ?? $orderNumber,
                'amount'    => $order->total,
                'paid'      => true,
                'reference' => $payload,
            ]);

            StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

            Log::channel('payment')->info('alipay.notify.success', ['order_number' => $orderNumber]);

            return $service->callbackSuccessResponse();
        } catch (\Throwable $e) {
            Log::channel('payment')->error('alipay.notify.failed', ['error' => $e->getMessage()]);

            return response('fail', 500);
        }
    }
}
