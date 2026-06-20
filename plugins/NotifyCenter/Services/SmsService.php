<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\NotifyCenter\Services;

use Illuminate\Support\Facades\Log;
use Overtrue\EasySms\EasySms;
use Throwable;

/**
 * 短信发送服务，基于 overtrue/easy-sms（主程序已集成）。
 */
class SmsService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public static function ready(): bool
    {
        return class_exists(EasySms::class);
    }

    /**
     * 发送模板短信。
     *
     * @param  string  $mobile
     * @param  string  $templateId  网关模板ID/Code
     * @param  array   $data        模板变量
     * @return bool
     */
    public function send(string $mobile, string $templateId, array $data = []): bool
    {
        if (! (bool) plugin_setting('notify_center', 'enable_sms', false)) {
            return false;
        }
        if ($mobile === '' || $templateId === '') {
            return false;
        }
        if (! self::ready()) {
            Log::warning('notify_center.sms.sdk_missing', ['message' => 'overtrue/easy-sms not installed']);

            return false;
        }

        $gateway = (string) plugin_setting('notify_center', 'sms_gateway', 'aliyun');

        try {
            $easySms = new EasySms($this->buildConfig($gateway));

            $easySms->send($mobile, [
                'template' => $templateId,
                'data'     => $data,
                // 部分网关(如阿里云)需要内容/签名，统一在 data/config 中处理
            ]);

            return true;
        } catch (Throwable $e) {
            Log::channel('order')->error('notify_center.sms.failed', [
                'mobile' => $mobile, 'template' => $templateId, 'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function buildConfig(string $gateway): array
    {
        return [
            'default' => [
                'gateways' => [$gateway],
            ],
            'gateways' => [
                'aliyun' => [
                    'access_key_id'     => (string) plugin_setting('notify_center', 'sms_access_key_id'),
                    'access_key_secret' => (string) plugin_setting('notify_center', 'sms_access_key_secret'),
                    'sign_name'         => (string) plugin_setting('notify_center', 'sms_sign_name'),
                ],
                'qcloud' => [
                    'sdk_app_id' => (string) plugin_setting('notify_center', 'sms_access_key_id'),
                    'secret_id'  => (string) plugin_setting('notify_center', 'sms_access_key_id'),
                    'secret_key' => (string) plugin_setting('notify_center', 'sms_access_key_secret'),
                    'sign_name'  => (string) plugin_setting('notify_center', 'sms_sign_name'),
                ],
            ],
        ];
    }
}
