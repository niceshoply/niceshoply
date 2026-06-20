<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Stripe;

use Plugin\Stripe\Services\StripeService;
use Stripe\Exception\ApiErrorException;

class Boot
{
    /**
     * https://uniapp.dcloud.net.cn/tutorial/app-payment-stripe.html
     *
     * @throws ApiErrorException
     * @throws \Exception
     */
    public function init(): void
    {
        // REST API / App：Stripe PaymentSheet
        listen_hook_filter('service.payment.api.stripe.data', function ($data) {
            $order = $data['order'] ?? null;
            if (! $order || ($order->billing_method_code ?? '') !== 'stripe') {
                return $data;
            }

            try {
                $mobile         = (new StripeService($order))->getMobilePaymentData();
                $data['params'] = [
                    'payment_intent_client_secret' => $mobile['paymentIntent'] ?? '',
                    'publishable_key'              => $mobile['publishKey'] ?? plugin_setting('stripe.publishable_key'),
                ];
            } catch (\Throwable $e) {
                $data['error'] = $e->getMessage();
            }

            return $data;
        });

        listen_hook_filter('service.payment.mobile_pay.data', function ($data) {
            $order = $data['order'];
            if ($order->payment_method_code != 'stripe') {
                return $data;
            }

            $data['params'] = (new StripeService($order))->getMobilePaymentData();

            return $data;
        });
    }
}
