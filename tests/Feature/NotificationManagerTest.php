<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use NiceShoply\Common\Services\Notification\NotificationChannelInterface;
use NiceShoply\Common\Services\Notification\NotificationManager;
use Tests\TestCase;

/**
 * 统一通知管理器测试（IMP-06）
 */
class NotificationManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        NotificationManager::resetInstance();
    }

    protected function tearDown(): void
    {
        NotificationManager::resetInstance();
        parent::tearDown();
    }

    /**
     * 注册渠道后 notify 应广播到所有渠道。
     */
    public function test_notify_broadcasts_to_all_channels(): void
    {
        $manager = NotificationManager::getInstance();

        $received = [];
        $manager->registerChannel('a', $this->makeChannel($received, 'A'));
        $manager->registerChannel('b', $this->makeChannel($received, 'B'));

        $this->assertTrue($manager->hasChannel('a'));
        $this->assertSame(['a', 'b'], $manager->getChannels());

        $manager->notify('标题', '内容', 'warning');

        $this->assertSame(['A:标题', 'B:标题'], $received);
    }

    /**
     * 单一渠道抛异常不影响其他渠道。
     */
    public function test_failing_channel_does_not_break_others(): void
    {
        $manager = NotificationManager::getInstance();

        $received = [];
        $failing  = new class implements NotificationChannelInterface
        {
            public function send(string $title, string $content, string $level = 'info'): void
            {
                throw new \RuntimeException('boom');
            }
        };

        $manager->registerChannel('failing', $failing);
        $manager->registerChannel('ok', $this->makeChannel($received, 'OK'));

        $manager->notify('T', 'C');

        $this->assertSame(['OK:T'], $received);
    }

    /**
     * 构造一个记录调用的测试渠道。
     */
    private function makeChannel(array &$received, string $tag): NotificationChannelInterface
    {
        return new class($received, $tag) implements NotificationChannelInterface
        {
            public function __construct(private array &$received, private string $tag) {}

            public function send(string $title, string $content, string $level = 'info'): void
            {
                $this->received[] = $this->tag.':'.$title;
            }
        };
    }
}
