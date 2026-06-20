<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Notification;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Laravel\Horizon\Events\LongWaitDetected;
use NiceShoply\Common\Events\OrderPlaced;
use NiceShoply\Common\Models\Order;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * 通知事件订阅器
 *
 * 将系统级事件（队列任务失败、队列长时间等待、应用异常）与
 * 业务事件（新订单、低库存）转换为统一通知，并根据后台「通知设置」
 * 从系统配置自动注册外部 webhook 渠道。
 */
class NotificationEventSubscriber
{
    /**
     * 注册触发通知的系统事件监听。
     *
     * @return void
     */
    public static function register(): void
    {
        // 依据后台设置注册外部通知渠道（webhook）
        self::bootChannelsFromSettings();

        // 注册业务事件监听（新订单等）
        self::registerBusinessEvents();

        Queue::failing(function (JobFailed $event) {
            try {
                $jobName   = $event->job->resolveName();
                $queue     = $event->job->getQueue();
                $attempts  = $event->job->attempts();
                $exception = $event->exception;

                $content = "> **Job:** {$jobName}\n"
                         ."> **Queue:** {$queue}\n"
                         ."> **Attempts:** {$attempts}\n"
                         .'> **Error:** '.$exception->getMessage()."\n"
                         .'> **File:** '.$exception->getFile().':'.$exception->getLine();

                NotificationManager::getInstance()->notify('队列任务失败', $content, 'error');
            } catch (Throwable $e) {
                Log::warning('发送队列失败通知出错：'.$e->getMessage());
            }
        });

        if (class_exists(LongWaitDetected::class)) {
            app('events')->listen(LongWaitDetected::class, function ($event) {
                try {
                    $content = "> **Connection:** {$event->connection}\n"
                             ."> **Queue:** {$event->queue}\n"
                             ."> **Wait:** {$event->seconds}s";

                    NotificationManager::getInstance()->notify('队列长时间等待', $content, 'warning');
                } catch (Throwable $e) {
                    Log::warning('发送队列等待通知出错：'.$e->getMessage());
                }
            });
        }
    }

    /**
     * 发送异常通知（由 bootstrap/app.php 的 reportable 回调调用）。
     *
     * @param  Throwable  $e
     * @return void
     */
    public static function notifyException(Throwable $e): void
    {
        // 404 与 4xx 客户端错误不告警，避免噪声
        if ($e instanceof NotFoundHttpException) {
            return;
        }
        if ($e instanceof HttpException && $e->getStatusCode() < 500) {
            return;
        }

        try {
            $content = '> **Exception:** '.get_class($e)."\n"
                     .'> **Message:** '.$e->getMessage()."\n"
                     .'> **File:** '.$e->getFile().':'.$e->getLine()."\n"
                     .'> **URL:** '.request()->fullUrl();

            NotificationManager::getInstance()->notify('应用异常', $content, 'error');
        } catch (Throwable $notifyException) {
            Log::warning('发送异常通知出错：'.$notifyException->getMessage());
        }
    }

    /**
     * 根据后台「通知设置」自动注册外部 webhook 渠道。
     *
     * 设置项：
     *  - notification_enabled       是否启用外部通知
     *  - notification_webhook_url   webhook 地址
     *  - notification_webhook_type  目标平台（slack/wechat_work/dingtalk/generic）
     *
     * @return void
     */
    public static function bootChannelsFromSettings(): void
    {
        if (! function_exists('system_setting')) {
            return;
        }

        if (! system_setting('notification_enabled', false)) {
            return;
        }

        $url = trim((string) system_setting('notification_webhook_url', ''));
        if ($url === '') {
            return;
        }

        $type = (string) system_setting('notification_webhook_type', 'generic');

        NotificationManager::getInstance()->registerChannel(
            'webhook',
            new WebhookNotificationChannel($url, $type)
        );
    }

    /**
     * 注册业务事件监听（如新订单）。
     *
     * @return void
     */
    public static function registerBusinessEvents(): void
    {
        app('events')->listen(OrderPlaced::class, function (OrderPlaced $event) {
            self::notifyNewOrder($event->order);
        });
    }

    /**
     * 发送「新订单」通知（受 notification_events 开关控制）。
     *
     * @param  Order  $order
     * @return void
     */
    public static function notifyNewOrder(Order $order): void
    {
        if (! NotificationManager::isEventEnabled('new_order')) {
            return;
        }

        try {
            $content = "> **订单号:** {$order->number}\n"
                     .'> **金额:** '.$order->total."\n"
                     .'> **客户:** '.($order->customer_name ?: $order->email);

            NotificationManager::getInstance()->notify('新订单', $content, 'info');
        } catch (Throwable $e) {
            Log::warning('发送新订单通知出错：'.$e->getMessage());
        }
    }

    /**
     * 发送「低库存」聚合通知（受 notification_events 开关控制）。
     *
     * @param  Collection|array  $stocks  低库存记录集合（含 warehouse 关系）
     * @return void
     */
    public static function notifyLowStock(Collection|array $stocks): void
    {
        if (! NotificationManager::isEventEnabled('low_stock')) {
            return;
        }

        $stocks = $stocks instanceof Collection ? $stocks : collect($stocks);
        if ($stocks->isEmpty()) {
            return;
        }

        try {
            // 仅展示前 20 条，避免消息体过长
            $lines = $stocks->take(20)->map(function ($stock) {
                $warehouse = $stock->warehouse->name ?? '-';

                return "> {$warehouse} / {$stock->sku_code}：{$stock->quantity} ≤ {$stock->low_stock_threshold}";
            })->implode("\n");

            $more    = $stocks->count() > 20 ? "\n> …… 等共 {$stocks->count()} 项" : '';
            $content = "共 {$stocks->count()} 个 SKU 低于预警阈值：\n".$lines.$more;

            NotificationManager::getInstance()->notify('库存预警', $content, 'warning');
        } catch (Throwable $e) {
            Log::warning('发送低库存通知出错：'.$e->getMessage());
        }
    }
}
