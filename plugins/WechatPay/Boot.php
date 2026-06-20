<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Plugin\WechatPay;

use Illuminate\Support\Facades\Log;
use Plugin\WechatPay\Services\WechatPayService;

class Boot
{
    public function init(): void
    {
        // App REST：native 优先，失败回退 H5 WebView
        listen_hook_filter('service.payment.api.wechat_pay.data', function ($data) {
            $order = $data['order'] ?? null;
            if (! $order || ($order->billing_method_code ?? '') !== 'wechat_pay') {
                return $data;
            }

            $channel = (string) ($data['payment_channel'] ?? 'auto');

            try {
                $data['params'] = (new WechatPayService($order))->getMobilePaymentData($channel);
            } catch (\Throwable $e) {
                Log::channel('payment')->error('wechat_pay.api.mobile.failed', [
                    'order_number' => $order->number ?? '',
                    'error'        => $e->getMessage(),
                ]);
                $data['params'] = [
                    'payment_url' => front_route('orders.pay', ['number' => $order->number]),
                ];
            }

            return $data;
        });

        // 前台 Web 支付页：注入 H5 跳转链接
        listen_hook_filter('service.payment.pay.wechat_pay.data', function ($paymentData) {
            $order = $paymentData['order'] ?? null;
            if (! $order) {
                return $paymentData;
            }

            try {
                $mobile = (new WechatPayService($order))->getMobilePaymentData('h5');
                $paymentData['h5_url'] = $mobile['mweb_url'] ?? $mobile['payment_url'] ?? '';
            } catch (\Throwable) {
                // 保留占位页
            }

            return $paymentData;
        });

        listen_hook_filter('service.payment.pay.wechat_pay.view', function () {
            return 'WechatPay::payment';
        });
    }
}
