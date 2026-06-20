<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Paypal\Services;

use Exception;
use NiceShoply\Common\Libraries\Currency;
use NiceShoply\Front\Services\PaymentService;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

/**
 * PayPal 支付服务（基于 PayPal Orders v2 API）。
 *
 * 采用「跳转支付」模式：
 *   1. createPaypalOrder() 在 PayPal 创建订单，返回 approve 跳转链接；
 *   2. 买家在 PayPal 完成授权后，浏览器回跳商城 return 路由；
 *   3. captureOrder() 服务端扣款，status=COMPLETED 即支付成功；
 *   4. 另由 Webhook 异步兜底确认（防止用户关闭回跳页导致漏单）。
 */
class PaypalService extends PaymentService
{
    /**
     * PayPal 零位小数货币（金额必须为整数字符串，不能带小数）。
     * 参考：https://developer.paypal.com/docs/api/reference/currency-codes/
     */
    public const ZERO_DECIMAL = [
        'HUF', 'JPY', 'TWD',
    ];

    /**
     * PayPal 支持的结算货币（不在列表内的货币需在下单时换算为支持货币）。
     */
    public const SUPPORTED_CURRENCIES = [
        'AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS',
        'INR', 'JPY', 'MYR', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'RUB',
        'SGD', 'SEK', 'CHF', 'THB', 'TWD', 'USD',
    ];

    private PayPalClient $client;

    /**
     * @param  mixed  $order
     * @throws Exception
     */
    public function __construct($order)
    {
        parent::__construct($order);

        // 客户端结算货币以订单货币为准（不支持时回退 USD）。
        $currency = strtoupper((string) $this->order->currency_code);
        if (! in_array($currency, self::SUPPORTED_CURRENCIES, true)) {
            $currency = 'USD';
        }

        $this->client = self::makeClient($currency);
    }

    /**
     * 构建并初始化 PayPal API 客户端（含获取访问令牌）。
     *
     * 该方法为静态工厂，便于 Webhook 回调等无订单上下文场景复用。
     *
     * @param  string  $currency  默认结算货币
     * @return PayPalClient
     * @throws Exception
     */
    public static function makeClient(string $currency = 'USD'): PayPalClient
    {
        $mode         = plugin_setting('paypal.mode') ?: 'sandbox';
        $clientId     = (string) plugin_setting('paypal.client_id');
        $clientSecret = (string) plugin_setting('paypal.client_secret');

        if ($clientId === '' || $clientSecret === '') {
            throw new Exception('Invalid PayPal credentials: client_id / client_secret is empty');
        }

        $currency = strtoupper($currency);
        if (! in_array($currency, self::SUPPORTED_CURRENCIES, true)) {
            $currency = 'USD';
        }

        // setApiCredentials 要求完整的配置结构，缺字段会抛异常。
        $config = [
            'mode'    => $mode === 'live' ? 'live' : 'sandbox',
            'sandbox' => [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'app_id'        => '',
            ],
            'live' => [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'app_id'        => '',
            ],
            'payment_action' => 'Sale',
            'currency'       => $currency,
            'notify_url'     => '',
            'locale'         => 'en_US',
            'validate_ssl'   => true,
        ];

        $client = new PayPalClient;
        $client->setApiCredentials($config);
        $client->getAccessToken();

        return $client;
    }

    /**
     * 计算 PayPal 下单金额（按订单货币汇率换算 + 货币精度格式化）。
     *
     * @return array{currency_code: string, value: string}
     */
    public function buildAmount(): array
    {
        $currency = strtoupper((string) $this->order->currency_code);

        // 订单总额按下单时汇率换算为展示货币金额。
        $total = Currency::getInstance()->convertByRate($this->order->total, $this->order->currency_value);

        // 不支持的货币回退 USD（金额保持换算后的数值，PayPal 侧按 USD 结算）。
        if (! in_array($currency, self::SUPPORTED_CURRENCIES, true)) {
            $currency = 'USD';
        }

        // 零位小数货币金额取整，其余保留两位小数。
        if (in_array($currency, self::ZERO_DECIMAL, true)) {
            $value = (string) (int) round((float) $total);
        } else {
            $value = number_format((float) $total, 2, '.', '');
        }

        return [
            'currency_code' => $currency,
            'value'         => $value,
        ];
    }

    /**
     * 在 PayPal 创建订单，返回完整响应（含 id 与 approve 链接）。
     *
     * @param  string  $returnUrl  授权成功回跳地址
     * @param  string  $cancelUrl  取消支付回跳地址
     * @return array
     * @throws \Throwable
     */
    public function createPaypalOrder(string $returnUrl, string $cancelUrl): array
    {
        $amount = $this->buildAmount();

        return $this->client->createOrder([
            'intent'         => 'CAPTURE',
            'purchase_units' => [[
                // custom_id 写入商城订单号，回调/捕获时用于反查订单。
                'custom_id'   => (string) $this->order->number,
                'invoice_id'  => (string) $this->order->number,
                'description' => 'Order #'.$this->order->number,
                'amount'      => [
                    'currency_code' => $amount['currency_code'],
                    'value'         => $amount['value'],
                ],
            ]],
            'application_context' => [
                'brand_name'          => system_setting('base.meta_title') ?: 'NiceShoply',
                'locale'              => 'en_US',
                'landing_page'        => 'LOGIN',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action'         => 'PAY_NOW',
                'return_url'          => $returnUrl,
                'cancel_url'          => $cancelUrl,
            ],
        ]);
    }

    /**
     * 捕获（扣款）指定 PayPal 订单。
     *
     * @param  string  $paypalOrderId
     * @return array
     * @throws \Throwable
     */
    public function captureOrder(string $paypalOrderId): array
    {
        return $this->client->capturePaymentOrder($paypalOrderId);
    }

    /**
     * 查询 PayPal 订单详情。
     *
     * @param  string  $paypalOrderId
     * @return array
     * @throws \Throwable
     */
    public function showOrder(string $paypalOrderId): array
    {
        return $this->client->showOrderDetails($paypalOrderId);
    }

    /**
     * 从 createOrder 响应中提取买家审批跳转链接。
     *
     * @param  array  $response
     * @return string
     */
    public static function extractApproveUrl(array $response): string
    {
        foreach ($response['links'] ?? [] as $link) {
            if (($link['rel'] ?? '') === 'approve' && ! empty($link['href'])) {
                return (string) $link['href'];
            }
        }

        return '';
    }
}
