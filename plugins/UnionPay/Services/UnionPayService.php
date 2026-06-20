<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\UnionPay\Services;

use Exception;
use NiceShoply\Common\Models\Order;
use Yansongda\Pay\Pay;

/**
 * 银联支付服务（基于 yansongda/pay v3 unipay）。
 *
 * 注意：需通过 composer 安装 yansongda/pay，并开通银联商户资质。
 * 证书私钥/公钥以 PEM 文本存于插件配置，运行时写入临时文件供 SDK 读取。
 * 具体下单参数请以银联与 yansongda/pay 文档为准，安装后按实际调整。
 */
class UnionPayService
{
    protected ?Order $order;

    public function __construct(?Order $order = null)
    {
        $this->order = $order;
    }

    public static function getInstance(?Order $order = null): static
    {
        return new static($order);
    }

    public static function ready(): bool
    {
        return class_exists(Pay::class);
    }

    protected function config(): array
    {
        return [
            'unipay' => [
                'default' => [
                    'mch_id'                 => (string) plugin_setting('union_pay', 'mch_id'),
                    'mch_cert_serial_no'     => (string) plugin_setting('union_pay', 'mch_cert_serial_no'),
                    'mch_secret_cert'        => $this->writeTempPem('private', (string) plugin_setting('union_pay', 'mch_private_cert')),
                    'mch_public_cert_path'   => $this->writeTempPem('public', (string) plugin_setting('union_pay', 'mch_public_cert')),
                    'unipay_public_cert_path' => $this->writeTempPem('platform', (string) plugin_setting('union_pay', 'unipay_public_cert')),
                    'return_url'             => $this->returnUrl(),
                    'notify_url'             => $this->notifyUrl(),
                    'mode'                   => ((int) plugin_setting('union_pay', 'sandbox', 0)) === 1 ? Pay::MODE_SANDBOX : Pay::MODE_NORMAL,
                ],
            ],
            'logger' => ['enable' => false],
        ];
    }

    protected function writeTempPem(string $name, string $content): string
    {
        if (trim($content) === '') {
            return '';
        }

        $dir = storage_path('app/union_pay');
        if (! is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        $path = $dir.'/'.$name.'_'.md5($content).'.pem';
        if (! file_exists($path)) {
            file_put_contents($path, $content);
            @chmod($path, 0600);
        }

        return $path;
    }

    /**
     * 网关网页(PC)支付：返回自动提交表单 HTML。
     *
     * @throws Exception
     */
    public function web(): string
    {
        $this->assertReady();
        Pay::config($this->config());

        $response = Pay::unipay()->web([
            'out_trade_no' => $this->order->number,
            'txn_amt'      => $this->amountFen(),
            'order_desc'   => 'Order '.$this->order->number,
        ]);

        return method_exists($response, 'getContent') ? (string) $response->getContent() : (string) $response;
    }

    /**
     * 手机(WAP)支付：返回自动提交表单 HTML。
     *
     * @throws Exception
     */
    public function wap(): string
    {
        $this->assertReady();
        Pay::config($this->config());

        $response = Pay::unipay()->wap([
            'out_trade_no' => $this->order->number,
            'txn_amt'      => $this->amountFen(),
            'order_desc'   => 'Order '.$this->order->number,
        ]);

        return method_exists($response, 'getContent') ? (string) $response->getContent() : (string) $response;
    }

    /**
     * 验证并解析银联回调。
     *
     * @throws Exception
     */
    public function verifyCallback(): array
    {
        $this->assertReady();
        Pay::config($this->config());

        return Pay::unipay()->callback()->toArray();
    }

    public function callbackSuccessResponse()
    {
        return Pay::unipay()->success();
    }

    protected function amountFen(): int
    {
        return (int) round(((float) $this->order->total) * 100);
    }

    protected function notifyUrl(): string
    {
        return route('union_pay.notify');
    }

    protected function returnUrl(): string
    {
        return front_route('payment.success');
    }

    /**
     * @throws Exception
     */
    protected function assertReady(): void
    {
        if (! self::ready()) {
            throw new Exception('yansongda/pay is not installed. Run: composer require yansongda/pay');
        }
    }
}
