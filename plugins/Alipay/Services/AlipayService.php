<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Alipay\Services;

use Exception;
use NiceShoply\Common\Models\Order;
use Yansongda\Pay\Pay;

/**
 * 支付宝支付服务（基于 yansongda/pay v3）。
 */
class AlipayService
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
            'alipay' => [
                'default' => [
                    'app_id'                 => (string) plugin_setting('alipay', 'app_id'),
                    'app_secret_cert'        => (string) plugin_setting('alipay', 'app_secret_cert'),
                    'app_public_cert_path'   => $this->writeTempPem('app_public', (string) plugin_setting('alipay', 'app_public_cert')),
                    'alipay_public_cert_path'=> $this->writeTempPem('alipay_public', (string) plugin_setting('alipay', 'alipay_public_cert')),
                    'alipay_root_cert_path'  => $this->writeTempPem('alipay_root', (string) plugin_setting('alipay', 'alipay_root_cert')),
                    'return_url'             => front_route('payment.success'),
                    'notify_url'             => route('alipay.notify'),
                    'mode'                   => ((int) plugin_setting('alipay', 'sandbox', 0)) === 1 ? Pay::MODE_SANDBOX : Pay::MODE_NORMAL,
                ],
            ],
            'logger' => [
                'enable' => false,
            ],
        ];
    }

    protected function writeTempPem(string $name, string $content): string
    {
        if (trim($content) === '') {
            return '';
        }

        $dir = storage_path('app/alipay');
        if (! is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        $path = $dir.'/'.$name.'_'.md5($content).'.crt';
        if (! file_exists($path)) {
            file_put_contents($path, $content);
            @chmod($path, 0600);
        }

        return $path;
    }

    /**
     * 电脑网站支付，返回自动提交表单 HTML。
     *
     * @throws Exception
     */
    public function web(): string
    {
        $this->assertReady();
        Pay::config($this->config());

        $response = Pay::alipay()->web([
            'out_trade_no' => $this->order->number,
            'total_amount' => $this->amountYuan(),
            'subject'      => 'Order '.$this->order->number,
        ]);

        return $response->getContent() ?: '';
    }

    /**
     * 手机网站(WAP)支付，返回跳转 HTML。
     *
     * @throws Exception
     */
    public function wap(): string
    {
        $this->assertReady();
        Pay::config($this->config());

        $response = Pay::alipay()->wap([
            'out_trade_no' => $this->order->number,
            'total_amount' => $this->amountYuan(),
            'subject'      => 'Order '.$this->order->number,
        ]);

        return $response->getContent() ?: '';
    }

    /**
     * App 支付，返回客户端调起字符串。
     *
     * @throws Exception
     */
    public function app(): array
    {
        $this->assertReady();
        Pay::config($this->config());

        $result = Pay::alipay()->app([
            'out_trade_no' => $this->order->number,
            'total_amount' => $this->amountYuan(),
            'subject'      => 'Order '.$this->order->number,
        ]);

        return ['order_string' => $result->getContent() ?: ''];
    }

    /**
     * 当面付扫码，返回 qr_code。
     *
     * @throws Exception
     */
    public function scan(): array
    {
        $this->assertReady();
        Pay::config($this->config());

        $result = Pay::alipay()->scan([
            'out_trade_no' => $this->order->number,
            'total_amount' => $this->amountYuan(),
            'subject'      => 'Order '.$this->order->number,
        ]);

        return ['qr_code' => $result['qr_code'] ?? ''];
    }

    /**
     * 验证并解析支付宝回调。
     *
     * @throws Exception
     */
    public function verifyCallback(): array
    {
        $this->assertReady();
        Pay::config($this->config());

        return Pay::alipay()->callback()->toArray();
    }

    public function callbackSuccessResponse()
    {
        return Pay::alipay()->success();
    }

    protected function amountYuan(): string
    {
        return number_format((float) $this->order->total, 2, '.', '');
    }

    /**
     * 是否优先 App 原生。
     */
    protected function preferNativeChannel(string $channel): bool
    {
        if ($channel === 'native') {
            return true;
        }
        if ($channel === 'h5') {
            return false;
        }

        $platform = strtolower((string) request()->header('X-Client-Platform', ''));

        return in_array($platform, ['ios', 'android'], true);
    }

    /**
     * 支付宝 App 原生 orderString。
     *
     * @return array<string, string>
     */
    public function getNativePaymentData(): array
    {
        $orderString = (string) ($this->app()['order_string'] ?? '');

        return [
            'alipay_order_string' => $orderString,
        ];
    }

    /**
     * App REST / WebView 支付参数（native 优先，失败回退 WAP）。
     *
     * @return array<string, string>
     */
    public function getMobilePaymentData(string $channel = 'auto'): array
    {
        $fallback = front_route('orders.pay', ['number' => $this->order->number]);

        if (! self::ready()) {
            return ['payment_url' => $fallback];
        }

        if ($this->preferNativeChannel($channel)) {
            try {
                $native = $this->getNativePaymentData();
                if (($native['alipay_order_string'] ?? '') !== '') {
                    return $native;
                }
            } catch (\Throwable) {
                // 回退 WAP
            }
        }

        return [
            'payment_url' => front_route('alipay_wap', ['number' => $this->order->number]),
        ];
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
