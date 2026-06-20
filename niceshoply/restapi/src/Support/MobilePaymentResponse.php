<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * 将 PaymentService::apiPay() 结果转为 App 期望字段（payment_url / payment_intent_client_secret）
 */

namespace NiceShoply\RestAPI\Support;

class MobilePaymentResponse
{
    /**
     * @param  array<string, mixed>  $apiPay
     * @return array<string, mixed>
     */
    public static function fromApiPay(array $apiPay): array
    {
        $params = is_array($apiPay['billing_params'] ?? null) ? $apiPay['billing_params'] : [];
        $orderNumber = (string) ($apiPay['order_number'] ?? '');

        $result = [
            'order_id'            => $apiPay['order_id'] ?? null,
            'order_number'        => $orderNumber,
            'billing_method_code' => $apiPay['billing_method_code'] ?? '',
            'billing_method_name' => $apiPay['billing_method_name'] ?? '',
        ];

        $clientSecret = $params['payment_intent_client_secret']
            ?? $params['paymentIntent']
            ?? $params['client_secret']
            ?? null;

        if ($clientSecret) {
            $result['payment_intent_client_secret'] = $clientSecret;
        }

        $paymentUrl = $params['payment_url']
            ?? $params['approve_url']
            ?? $params['mweb_url']
            ?? null;

        if (! $paymentUrl && $orderNumber !== '' && function_exists('front_route')) {
            $paymentUrl = front_route('orders.pay', ['number' => $orderNumber]);
        }

        if ($paymentUrl) {
            $result['payment_url'] = $paymentUrl;
        }

        // 微信 App 原生支付参数
        $wechatApp = $params['wechat_app'] ?? null;
        if (is_array($wechatApp) && $wechatApp !== []) {
            $result['wechat_app'] = $wechatApp;
        }

        // 支付宝 App 原生 orderString
        $alipayOrderString = $params['alipay_order_string'] ?? $params['order_string'] ?? null;
        if (is_string($alipayOrderString) && $alipayOrderString !== '') {
            $result['alipay_order_string'] = $alipayOrderString;
        }

        if (! empty($params)) {
            $result['billing_params'] = $params;
        }

        return $result;
    }
}
