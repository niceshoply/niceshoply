<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Services\Notification\NotificationChannelInterface;
use NiceShoply\Common\Services\Notification\NotificationEventSubscriber;
use NiceShoply\Common\Services\Notification\NotificationManager;
use NiceShoply\Common\Services\Notification\WebhookNotificationChannel;
use Tests\TestCase;

/**
 * 通知系统业务事件接入测试（IMP-06）。
 *
 * 覆盖：
 *  - 新订单/低库存事件在开关启用时推送、关闭时不推送
 *  - 依据系统设置自动注册 webhook 渠道
 *  - WebhookNotificationChannel 按平台构造消息体并发送
 */
class NotificationBusinessEventsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        NotificationManager::resetInstance();
        // 默认关闭所有业务事件，避免相互影响
        config(['nice.system.notification_events' => []]);
    }

    protected function tearDown(): void
    {
        NotificationManager::resetInstance();
        parent::tearDown();
    }

    /**
     * 注册一个记录调用的测试渠道。
     */
    private function spyChannel(array &$received): NotificationChannelInterface
    {
        return new class($received) implements NotificationChannelInterface
        {
            public function __construct(private array &$received) {}

            public function send(string $title, string $content, string $level = 'info'): void
            {
                $this->received[] = $title;
            }
        };
    }

    public function test_new_order_notifies_when_event_enabled(): void
    {
        config(['nice.system.notification_events' => ['new_order']]);

        $received = [];
        NotificationManager::getInstance()->registerChannel('spy', $this->spyChannel($received));

        $order                = new Order;
        $order->number        = 'N-1001';
        $order->total         = 88.00;
        $order->customer_name = '张三';

        NotificationEventSubscriber::notifyNewOrder($order);

        $this->assertSame(['新订单'], $received);
    }

    public function test_new_order_skipped_when_event_disabled(): void
    {
        config(['nice.system.notification_events' => []]);

        $received = [];
        NotificationManager::getInstance()->registerChannel('spy', $this->spyChannel($received));

        $order         = new Order;
        $order->number = 'N-1002';
        $order->total  = 10.00;

        NotificationEventSubscriber::notifyNewOrder($order);

        $this->assertSame([], $received);
    }

    public function test_low_stock_notifies_when_event_enabled(): void
    {
        config(['nice.system.notification_events' => ['low_stock']]);

        $received = [];
        NotificationManager::getInstance()->registerChannel('spy', $this->spyChannel($received));

        $stocks = collect([
            (object) [
                'sku_code'            => 'SKU-1',
                'quantity'            => 2,
                'low_stock_threshold' => 5,
                'warehouse'           => (object) ['name' => '主仓'],
            ],
        ]);

        NotificationEventSubscriber::notifyLowStock($stocks);

        $this->assertSame(['库存预警'], $received);
    }

    public function test_low_stock_skipped_when_empty(): void
    {
        config(['nice.system.notification_events' => ['low_stock']]);

        $received = [];
        NotificationManager::getInstance()->registerChannel('spy', $this->spyChannel($received));

        NotificationEventSubscriber::notifyLowStock(collect([]));

        $this->assertSame([], $received);
    }

    public function test_boot_channels_from_settings_registers_webhook(): void
    {
        config([
            'nice.system.notification_enabled'      => true,
            'nice.system.notification_webhook_url'  => 'https://hooks.example.com/x',
            'nice.system.notification_webhook_type' => 'slack',
        ]);

        NotificationEventSubscriber::bootChannelsFromSettings();

        $this->assertTrue(NotificationManager::getInstance()->hasChannel('webhook'));
    }

    public function test_boot_channels_skipped_when_disabled(): void
    {
        config([
            'nice.system.notification_enabled'     => false,
            'nice.system.notification_webhook_url' => 'https://hooks.example.com/x',
        ]);

        NotificationEventSubscriber::bootChannelsFromSettings();

        $this->assertFalse(NotificationManager::getInstance()->hasChannel('webhook'));
    }

    public function test_webhook_channel_posts_platform_payload(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);

        (new WebhookNotificationChannel('https://hooks.example.com/wecom', 'wechat_work'))
            ->send('库存预警', '内容', 'warning');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://hooks.example.com/wecom'
                && $request['msgtype'] === 'markdown'
                && str_contains($request['markdown']['content'], '库存预警');
        });
    }

    public function test_webhook_channel_noop_on_empty_url(): void
    {
        Http::fake();

        (new WebhookNotificationChannel('', 'slack'))->send('T', 'C');

        Http::assertNothingSent();
    }
}
