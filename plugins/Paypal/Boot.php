<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Paypal;

use Illuminate\Support\Facades\Log;
use Plugin\Paypal\Services\PaypalService;

class Boot
{
    public function init(): void
    {
        // REST API / 移动端结算：返回 PayPal 审批跳转链接及客户端初始化参数。
        listen_hook_filter('service.payment.api.paypal.data', function ($data) {
            $order = $data['order'] ?? null;
            if (! $order) {
                return $data;
            }

            try {
                $service  = new PaypalService($order);
                $response = $service->createPaypalOrder(
                    front_route('paypal_return', ['order_number' => $order->number]),
                    front_route('paypal_cancel', ['order_number' => $order->number]),
                );

                $data['params'] = [
                    'paypal_order_id' => $response['id'] ?? '',
                    'approve_url'     => PaypalService::extractApproveUrl($response),
                    'payment_url'     => PaypalService::extractApproveUrl($response),
                    'client_id'       => plugin_setting('paypal.client_id'),
                    'mode'            => plugin_setting('paypal.mode') ?: 'sandbox',
                ];
            } catch (\Throwable $e) {
                Log::channel('payment')->error('paypal.api.create_order.failed', [
                    'order_number' => $order->number ?? '',
                    'error'        => $e->getMessage(),
                ]);
                $data['error'] = $e->getMessage();
            }

            return $data;
        });
    }
}
