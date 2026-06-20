<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\NotifyCenter\Services;

use Plugin\NotifyCenter\Models\MemberNotification;

class NotifyService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 推送站内信。customerId=0 表示全员广播。
     */
    public function notify(int $customerId, string $title, string $content = '', string $type = 'system', int $refId = 0): MemberNotification
    {
        return MemberNotification::query()->create([
            'customer_id' => $customerId,
            'title'       => $title,
            'content'     => $content,
            'type'        => $type,
            'ref_id'      => $refId,
        ]);
    }

    /**
     * 某会员未读数量（含全员广播）。
     */
    public function unreadCount(int $customerId): int
    {
        return (int) MemberNotification::query()
            ->whereIn('customer_id', [$customerId, 0])
            ->whereNull('read_at')
            ->count();
    }

    public function markRead(int $customerId, int $notificationId): bool
    {
        return (bool) MemberNotification::query()
            ->where('id', $notificationId)
            ->whereIn('customer_id', [$customerId, 0])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllRead(int $customerId): int
    {
        return (int) MemberNotification::query()
            ->whereIn('customer_id', [$customerId, 0])
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * 处理订单事件，推送站内信并可选发送短信。
     */
    public function handleOrderEvent($order, string $event): void
    {
        if (! $order || ! (bool) plugin_setting('notify_center', 'notify_on_order', true)) {
            return;
        }

        $customerId = (int) ($order->customer_id ?? 0);
        if ($customerId <= 0) {
            return;
        }

        $number = (string) ($order->number ?? $order->id ?? '');
        [$title, $content, $smsTplKey] = $this->orderEventText($event, $number);
        if ($title === '') {
            return;
        }

        $this->notify($customerId, $title, $content, 'order', (int) ($order->id ?? 0));

        // 短信
        if ($smsTplKey !== '') {
            $templateId = (string) plugin_setting('notify_center', $smsTplKey, '');
            $mobile     = $this->resolveMobile($order);
            if ($templateId !== '' && $mobile !== '') {
                SmsService::getInstance()->send($mobile, $templateId, ['number' => $number]);
            }
        }
    }

    /**
     * @return array{0:string,1:string,2:string} [title, content, sms_template_setting_key]
     */
    protected function orderEventText(string $event, string $number): array
    {
        return match ($event) {
            'placed'  => [__('NotifyCenter::common.order_placed_title'), __('NotifyCenter::common.order_placed_content', ['number' => $number]), ''],
            'paid'    => [__('NotifyCenter::common.order_paid_title'), __('NotifyCenter::common.order_paid_content', ['number' => $number]), 'sms_tpl_order_paid'],
            'shipped' => [__('NotifyCenter::common.order_shipped_title'), __('NotifyCenter::common.order_shipped_content', ['number' => $number]), 'sms_tpl_order_shipped'],
            default   => ['', '', ''],
        };
    }

    protected function resolveMobile($order): string
    {
        $customer = $order->customer ?? null;
        if ($customer) {
            return (string) ($customer->mobile ?? $customer->phone ?? '');
        }

        return (string) ($order->phone ?? '');
    }
}
