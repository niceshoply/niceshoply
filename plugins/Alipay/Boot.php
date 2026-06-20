<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Plugin\Alipay;

use Illuminate\Support\Facades\Log;
use Plugin\Alipay\Services\AlipayService;

class Boot
{
    public function init(): void
    {
        listen_hook_filter('service.payment.api.alipay.data', function ($data) {
            $order = $data['order'] ?? null;
            if (! $order || ($order->billing_method_code ?? '') !== 'alipay') {
                return $data;
            }

            $channel = (string) ($data['payment_channel'] ?? 'auto');

            try {
                $data['params'] = (new AlipayService($order))->getMobilePaymentData($channel);
            } catch (\Throwable $e) {
                Log::channel('payment')->error('alipay.api.mobile.failed', [
                    'order_number' => $order->number ?? '',
                    'error'        => $e->getMessage(),
                ]);
                $data['params'] = [
                    'payment_url' => front_route('orders.pay', ['number' => $order->number]),
                ];
            }

            return $data;
        });

        listen_hook_filter('service.payment.pay.alipay.view', function () {
            return 'Alipay::payment';
        });
    }
}
