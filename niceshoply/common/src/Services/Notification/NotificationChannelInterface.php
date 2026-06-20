<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Notification;

/**
 * 通知渠道接口
 *
 * 各通知渠道（企业微信、钉钉、飞书、Slack、邮件等）实现该接口后，
 * 由 NotificationManager 统一调度发送。
 */
interface NotificationChannelInterface
{
    /**
     * 通过该渠道发送通知。
     *
     * @param  string  $title  通知标题
     * @param  string  $content  通知内容（支持 markdown）
     * @param  string  $level  级别：info、warning、error
     * @return void
     */
    public function send(string $title, string $content, string $level = 'info'): void;
}
