<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WechatPay\Services;

use Exception;
use NiceShoply\Common\Models\Order;
use Yansongda\Pay\Pay;

/**
 * 微信支付服务（基于 yansongda/pay v3）。
 *
 * 注意：需通过插件 composer.json 安装 yansongda/pay。
 * 证书私钥/平台证书建议存放于安全目录，这里支持直接读取插件配置中的 PEM 文本。
 */
class WechatPayService
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

    /**
     * SDK 是否就绪。
     */
    public static function ready(): bool
    {
        return class_exists(Pay::class);
    }

    /**
     * 组装 yansongda/pay v3 配置。
     */
    protected function config(): array
    {
        $mchId = (string) plugin_setting('wechat_pay', 'mch_id');

        return [
            'wechat' => [
                'default' => [
                    'mch_id'                   => $mchId,
                    'mch_secret_key'           => (string) plugin_setting('wechat_pay', 'api_v3_key'),
                    'mch_secret_cert'          => $this->writeTempPem('private', (string) plugin_setting('wechat_pay', 'cert_private')),
                    'mch_public_cert_path'     => $this->writeTempPem('public', (string) plugin_setting('wechat_pay', 'cert_public')),
                    'app_id'                   => (string) plugin_setting('wechat_pay', 'app_id'),
                    'mini_app_id'              => (string) plugin_setting('wechat_pay', 'mini_app_id'),
                    'wechat_public_cert_path'  => [
                        plugin_setting('wechat_pay', 'serial_no') => $this->writeTempPem('platform', (string) plugin_setting('wechat_pay', 'platform_cert')),
                    ],
                    'mode' => ((int) plugin_setting('wechat_pay', 'sandbox', 0)) === 1 ? Pay::MODE_SERVICE : Pay::MODE_NORMAL,
                ],
            ],
            'logger' => [
                'enable' => false,
            ],
        ];
    }

    /**
     * 将 PEM 文本写入临时文件并返回路径（SDK 需要文件路径）。
     */
    protected function writeTempPem(string $name, string $content): string
    {
        if (trim($content) === '') {
            return '';
        }

        $dir = storage_path('app/wechat_pay');
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
     * Native 扫码支付，返回 code_url 用于生成二维码。
     *
     * @throws Exception
     */
    public function native(): array
    {
        $this->assertReady();
        Pay::config($this->config());

        $result = Pay::wechat()->scan([
            'out_trade_no' => $this->order->number,
            'description'  => 'Order '.$this->order->number,
            'amount'       => ['total' => $this->amountFen(), 'currency' => 'CNY'],
            'notify_url'   => $this->notifyUrl(),
        ]);

        return ['code_url' => $result['code_url'] ?? ''];
    }

    /**
     * JSAPI / 小程序支付，返回前端调起参数。openid 必填。
     *
     * @throws Exception
     */
    public function jsapi(string $openid, bool $mini = false): array
    {
        $this->assertReady();
        Pay::config($this->config());

        $appId  = $mini
            ? (string) plugin_setting('wechat_pay', 'mini_app_id')
            : (string) plugin_setting('wechat_pay', 'app_id');

        $result = Pay::wechat()->mp([
            '_type'        => $mini ? 'mini' : 'mp',
            'out_trade_no' => $this->order->number,
            'description'  => 'Order '.$this->order->number,
            'amount'       => ['total' => $this->amountFen(), 'currency' => 'CNY'],
            'payer'        => ['openid' => $openid],
            'notify_url'   => $this->notifyUrl(),
            'app_id'       => $appId,
        ]);

        return $result->toArray();
    }

    /**
     * H5 支付，返回 h5_url。
     *
     * @throws Exception
     */
    public function h5(): array
    {
        $this->assertReady();
        Pay::config($this->config());

        $result = Pay::wechat()->h5([
            'out_trade_no' => $this->order->number,
            'description'  => 'Order '.$this->order->number,
            'amount'       => ['total' => $this->amountFen(), 'currency' => 'CNY'],
            'notify_url'   => $this->notifyUrl(),
            'scene_info'   => [
                'payer_client_ip' => request()->ip() ?: '127.0.0.1',
                'h5_info'         => [
                    'type'     => 'Wap',
                    'app_name' => (string) (config('app.name') ?: 'NiceShoply'),
                    'app_url'  => (string) (config('app.url') ?: url('/')),
                ],
            ],
        ]);

        return ['h5_url' => $result['h5_url'] ?? ''];
    }

    /**
     * 微信 App 支付，返回客户端调起参数。
     *
     * @throws Exception
     */
    public function app(): array
    {
        $this->assertReady();
        Pay::config($this->config());

        $result = Pay::wechat()->app([
            'out_trade_no' => $this->order->number,
            'description'  => 'Order '.$this->order->number,
            'amount'       => ['total' => $this->amountFen(), 'currency' => 'CNY'],
            'notify_url'   => $this->notifyUrl(),
        ]);

        $arr = $result->toArray();

        return [
            'app_id'     => (string) ($arr['appid'] ?? plugin_setting('wechat_pay', 'app_id')),
            'partner_id' => (string) ($arr['partnerid'] ?? plugin_setting('wechat_pay', 'mch_id')),
            'prepay_id'  => (string) ($arr['prepayid'] ?? ''),
            'package'    => (string) ($arr['package'] ?? 'Sign=WXPay'),
            'nonce_str'  => (string) ($arr['noncestr'] ?? ''),
            'timestamp'  => (string) ($arr['timestamp'] ?? ''),
            'sign'       => (string) ($arr['sign'] ?? ''),
        ];
    }

    /**
     * 是否优先走 App 原生（auto 时根据 X-Client-Platform 判断）。
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
     * 微信 App 原生支付参数。
     *
     * @return array<string, mixed>
     */
    public function getNativePaymentData(): array
    {
        return [
            'wechat_app' => $this->app(),
        ];
    }

    /**
     * App REST / WebView 支付参数（native 优先，失败回退 H5）。
     *
     * @return array<string, mixed>
     */
    public function getMobilePaymentData(string $channel = 'auto'): array
    {
        $fallback = front_route('orders.pay', ['number' => $this->order->number]);

        if (! self::ready()) {
            return ['payment_url' => $fallback];
        }

        if ($this->preferNativeChannel($channel)) {
            try {
                return $this->getNativePaymentData();
            } catch (\Throwable) {
                // 原生下单失败时回退 H5
            }
        }

        try {
            $h5Url = $this->h5()['h5_url'] ?? '';
            if ($h5Url !== '') {
                return [
                    'payment_url' => $h5Url,
                    'mweb_url'    => $h5Url,
                ];
            }
        } catch (\Throwable) {
            // SDK 未配置或下单失败时回退到前台支付页
        }

        return ['payment_url' => $fallback];
    }

    /**
     * 验证并解析微信回调，成功返回 out_trade_no，否则抛异常。
     *
     * @throws Exception
     */
    public function verifyCallback(): array
    {
        $this->assertReady();
        Pay::config($this->config());

        $collection = Pay::wechat()->callback();

        return $collection->toArray();
    }

    /**
     * 成功响应微信回调。
     */
    public function callbackSuccessResponse()
    {
        return Pay::wechat()->success();
    }

    protected function amountFen(): int
    {
        return (int) round(((float) $this->order->total) * 100);
    }

    protected function notifyUrl(): string
    {
        return route('wechat_pay.notify');
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
