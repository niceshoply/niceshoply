<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Notification;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * 统一通知管理器
 *
 * 以单例聚合多个通知渠道，调用 notify() 时广播到所有已注册渠道；
 * 单一渠道发送失败不影响其它渠道（异常被记录后吞掉）。
 */
class NotificationManager
{
    private static ?NotificationManager $instance = null;

    /** @var array<string, NotificationChannelInterface> 已注册渠道 */
    private array $channels = [];

    private function __construct() {}

    /**
     * 获取单例。
     *
     * @return NotificationManager
     */
    public static function getInstance(): NotificationManager
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * 注册一个通知渠道。
     *
     * @param  string  $name  渠道唯一名（如 wechat_work、slack、email）
     * @param  NotificationChannelInterface  $channel
     * @return void
     */
    public function registerChannel(string $name, NotificationChannelInterface $channel): void
    {
        $this->channels[$name] = $channel;
    }

    /**
     * 移除一个已注册渠道。
     *
     * @param  string  $name
     * @return void
     */
    public function removeChannel(string $name): void
    {
        unset($this->channels[$name]);
    }

    /**
     * 获取所有已注册渠道名。
     *
     * @return string[]
     */
    public function getChannels(): array
    {
        return array_keys($this->channels);
    }

    /**
     * 渠道是否已注册。
     *
     * @param  string  $name
     * @return bool
     */
    public function hasChannel(string $name): bool
    {
        return isset($this->channels[$name]);
    }

    /**
     * 向所有已注册渠道发送通知。
     *
     * @param  string  $title  标题
     * @param  string  $content  内容（支持 markdown）
     * @param  string  $level  级别：info、warning、error
     * @return void
     */
    public function notify(string $title, string $content, string $level = 'info'): void
    {
        if (empty($this->channels)) {
            return;
        }

        foreach ($this->channels as $name => $channel) {
            try {
                $channel->send($title, $content, $level);
            } catch (Throwable $e) {
                Log::warning("通知渠道 [{$name}] 发送失败：".$e->getMessage());
            }
        }
    }

    /**
     * 指定业务事件是否已在后台「通知设置」中启用。
     *
     * 业务事件开关存于系统设置 `notification_events`（多选数组），
     * 例如 new_order、low_stock、order_paid。
     *
     * @param  string  $event  业务事件标识
     * @return bool
     */
    public static function isEventEnabled(string $event): bool
    {
        if (! function_exists('system_setting')) {
            return false;
        }

        $enabled = system_setting('notification_events', []);
        if (! is_array($enabled)) {
            $enabled = [];
        }

        return in_array($event, $enabled, true);
    }

    /**
     * 重置单例（便于测试）。
     *
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }
}
