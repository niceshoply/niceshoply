<?php
namespace Plugin\GlobalPay\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use NiceShoply\Common\Models\Order;

class GlobalPayService
{
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public static function getInstance(Order $order): static
    {
        return new static($order);
    }

    public function provider(): string
    {
        return (string) plugin_setting('global_pay', 'provider', 'stripe');
    }

    public function sandbox(): bool
    {
        return (int) plugin_setting('global_pay', 'sandbox', 0) === 1;
    }

    /**
     * 创建支付跳转 URL（Stripe Checkout 或 PayPal approve link）。
     */
    public function createRedirectUrl(): string
    {
        return $this->provider() === 'paypal'
            ? $this->createPayPalOrder()
            : $this->createStripeSession();
    }

    protected function createStripeSession(): string
    {
        $secret = (string) plugin_setting('global_pay', 'stripe_secret', '');
        if ($secret === '') {
            throw new Exception(__('GlobalPay::common.no_stripe'));
        }

        $amount   = (int) round((float) $this->order->total * 100);
        $currency = strtolower((string) ($this->order->currency_code ?: setting_currency_code()));
        $success  = front_route('payment.success', ['order_number' => $this->order->number]);
        $cancel   = front_route('payment.cancel', ['order_number' => $this->order->number]);

        $resp = Http::withToken($secret)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'mode'                   => 'payment',
                'success_url'            => $success,
                'cancel_url'             => $cancel,
                'client_reference_id'    => $this->order->number,
                'metadata[order_number]' => $this->order->number,
                'line_items[0][price_data][currency]'     => $currency,
                'line_items[0][price_data][unit_amount]'  => max(1, $amount),
                'line_items[0][price_data][product_data][name]' => 'Order '.$this->order->number,
                'line_items[0][quantity]'                 => 1,
            ]);

        if (! $resp->successful()) {
            throw new Exception($resp->json('error.message') ?? __('GlobalPay::common.pay_failed'));
        }

        return (string) $resp->json('url');
    }

    protected function createPayPalOrder(): string
    {
        $clientId = (string) plugin_setting('global_pay', 'paypal_client_id', '');
        $secret   = (string) plugin_setting('global_pay', 'paypal_secret', '');
        if ($clientId === '' || $secret === '') {
            throw new Exception(__('GlobalPay::common.no_paypal'));
        }

        $base = $this->sandbox()
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $tokenResp = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->post("{$base}/v1/oauth2/token", ['grant_type' => 'client_credentials']);
        $token = (string) $tokenResp->json('access_token');
        if ($token === '') {
            throw new Exception(__('GlobalPay::common.pay_failed'));
        }

        $success = front_route('payment.success', ['order_number' => $this->order->number]);
        $cancel  = front_route('payment.cancel', ['order_number' => $this->order->number]);

        $orderResp = Http::withToken($token)
            ->post("{$base}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $this->order->number,
                    'amount' => [
                        'currency_code' => strtoupper((string) ($this->order->currency_code ?: setting_currency_code())),
                        'value'         => number_format((float) $this->order->total, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => $success,
                    'cancel_url' => $cancel,
                ],
            ]);

        $links = $orderResp->json('links') ?? [];
        foreach ($links as $link) {
            if (($link['rel'] ?? '') === 'approve') {
                return (string) ($link['href'] ?? '');
            }
        }

        throw new Exception(__('GlobalPay::common.pay_failed'));
    }

    public function notifyUrl(): string
    {
        return url('/callback/global_pay');
    }
}
