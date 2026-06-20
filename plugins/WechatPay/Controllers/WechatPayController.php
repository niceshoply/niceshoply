<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WechatPay\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Repositories\Order\PaymentRepo;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\StateMachineService;
use Plugin\WechatPay\Services\WechatPayService;

class WechatPayController extends Controller
{
    /**
     * 微信支付异步回调通知（验签 → 校验金额 → 标记订单已支付）。
     */
    public function notify()
    {
        try {
            $service = WechatPayService::getInstance();
            $payload = $service->verifyCallback();

            $resource    = $payload['resource'] ?? $payload;
            $orderNumber = $payload['out_trade_no'] ?? ($resource['out_trade_no'] ?? '');
            $tradeState  = $payload['trade_state'] ?? ($resource['trade_state'] ?? '');

            if ($orderNumber === '' || $tradeState !== 'SUCCESS') {
                return $service->callbackSuccessResponse();
            }

            $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);
            if (! $order) {
                Log::channel('payment')->warning('wechat_pay.notify.order_not_found', ['order_number' => $orderNumber]);

                return $service->callbackSuccessResponse();
            }

            // 幂等：已支付及之后状态直接确认
            if (in_array($order->status, StateMachineService::getValidStatuses(), true)) {
                return $service->callbackSuccessResponse();
            }

            // 金额校验（分）
            $paidFen     = (int) ($payload['amount']['total'] ?? ($resource['amount']['total'] ?? 0));
            $expectedFen = (int) round(((float) $order->total) * 100);
            if ($paidFen > 0 && abs($paidFen - $expectedFen) > 1) {
                Log::channel('payment')->warning('wechat_pay.notify.amount_mismatch', [
                    'order_number' => $orderNumber, 'paid' => $paidFen, 'expected' => $expectedFen,
                ]);

                return $service->callbackSuccessResponse();
            }

            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
                'charge_id' => $payload['transaction_id'] ?? ($resource['transaction_id'] ?? $orderNumber),
                'amount'    => $order->total,
                'paid'      => true,
                'reference' => $payload,
            ]);

            StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

            Log::channel('payment')->info('wechat_pay.notify.success', ['order_number' => $orderNumber]);

            return $service->callbackSuccessResponse();
        } catch (\Throwable $e) {
            Log::channel('payment')->error('wechat_pay.notify.failed', ['error' => $e->getMessage()]);

            return response('fail', 500);
        }
    }
}
