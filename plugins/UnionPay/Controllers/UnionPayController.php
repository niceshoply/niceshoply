<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\UnionPay\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Repositories\Order\PaymentRepo;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\StateMachineService;
use Plugin\UnionPay\Services\UnionPayService;

class UnionPayController extends Controller
{
    /**
     * 银联异步回调通知（验签 → 校验金额 → 标记订单已支付）。
     */
    public function notify()
    {
        try {
            $service = UnionPayService::getInstance();
            $payload = $service->verifyCallback();

            $orderNumber = (string) ($payload['out_trade_no'] ?? ($payload['orderId'] ?? ''));
            $status      = (string) ($payload['respCode'] ?? ($payload['trade_status'] ?? ''));

            // 银联成功响应码通常为 00；若 SDK 已验签通过且有订单号，则视为成功
            if ($orderNumber === '') {
                return $service->callbackSuccessResponse();
            }

            $order = OrderRepo::getInstance()->getOrderByNumber($orderNumber);
            if (! $order) {
                Log::channel('payment')->warning('union_pay.notify.order_not_found', ['order_number' => $orderNumber]);

                return $service->callbackSuccessResponse();
            }

            if (in_array($order->status, StateMachineService::getValidStatuses(), true)) {
                return $service->callbackSuccessResponse();
            }

            if ($status !== '' && ! in_array($status, ['00', 'success', 'SUCCESS', 'TRADE_SUCCESS'], true)) {
                return $service->callbackSuccessResponse();
            }

            // 金额校验（分）
            $paidFen     = (int) ($payload['txnAmt'] ?? ($payload['txn_amt'] ?? 0));
            $expectedFen = (int) round(((float) $order->total) * 100);
            if ($paidFen > 0 && abs($paidFen - $expectedFen) > 1) {
                Log::channel('payment')->warning('union_pay.notify.amount_mismatch', [
                    'order_number' => $orderNumber, 'paid' => $paidFen, 'expected' => $expectedFen,
                ]);

                return $service->callbackSuccessResponse();
            }

            PaymentRepo::getInstance()->createOrUpdatePayment($order->id, [
                'charge_id' => $payload['queryId'] ?? ($payload['transaction_id'] ?? $orderNumber),
                'amount'    => $order->total,
                'paid'      => true,
                'reference' => $payload,
            ]);

            StateMachineService::getInstance($order)->setShipment()->changeStatus(StateMachineService::PAID);

            Log::channel('payment')->info('union_pay.notify.success', ['order_number' => $orderNumber]);

            return $service->callbackSuccessResponse();
        } catch (\Throwable $e) {
            Log::channel('payment')->error('union_pay.notify.failed', ['error' => $e->getMessage()]);

            return response('fail', 500);
        }
    }
}
