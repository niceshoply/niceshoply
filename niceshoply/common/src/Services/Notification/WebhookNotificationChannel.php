<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Webhook 通知渠道。
 *
 * 通过 HTTP POST 将通知推送到外部 IM / 协作平台的入站机器人。
 * 内置常见平台的消息体格式，按 type 自动适配：
 *  - slack        Slack Incoming Webhook（text）
 *  - wechat_work  企业微信群机器人（markdown）
 *  - dingtalk     钉钉自定义机器人（markdown）
 *  - generic      通用 JSON（title/content/level），适配自建接收端
 */
class WebhookNotificationChannel implements NotificationChannelInterface
{
    public function __construct(
        private string $url,
        private string $type = 'generic',
        private int $timeout = 5,
    ) {}

    /**
     * 发送通知到 webhook。
     *
     * @param  string  $title  标题
     * @param  string  $content  内容（markdown）
     * @param  string  $level  级别：info / warning / error
     * @return void
     */
    public function send(string $title, string $content, string $level = 'info'): void
    {
        if (trim($this->url) === '') {
            return;
        }

        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->post($this->url, $this->buildPayload($title, $content, $level));

        if ($response->failed()) {
            Log::warning('Webhook 通知发送失败：HTTP '.$response->status());
        }
    }

    /**
     * 按目标平台构造消息体。
     *
     * @param  string  $title
     * @param  string  $content
     * @param  string  $level
     * @return array
     */
    private function buildPayload(string $title, string $content, string $level): array
    {
        $emoji = match ($level) {
            'error'   => '🔴',
            'warning' => '🟡',
            default   => '🟢',
        };

        return match ($this->type) {
            'slack' => [
                'text' => "{$emoji} *{$title}*\n{$content}",
            ],
            'wechat_work' => [
                'msgtype'  => 'markdown',
                'markdown' => ['content' => "{$emoji} **{$title}**\n{$content}"],
            ],
            'dingtalk' => [
                'msgtype'  => 'markdown',
                'markdown' => [
                    'title' => $title,
                    'text'  => "{$emoji} **{$title}**\n\n{$content}",
                ],
            ],
            default => [
                'title'   => $title,
                'content' => $content,
                'level'   => $level,
            ],
        };
    }
}
