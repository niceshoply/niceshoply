<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\NewsletterSubscriber;
use NiceShoply\Common\Services\NewsletterService;
use Tests\TestCase;

/**
 * Newsletter 订阅服务测试（IMP-07）
 */
class NewsletterServiceTest extends TestCase
{
    use DatabaseTransactions;

    private NewsletterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = NewsletterService::getInstance();
    }

    /**
     * 首次订阅应创建活跃记录。
     */
    public function test_subscribe_creates_active_record(): void
    {
        $email      = 'sub-'.uniqid().'@example.com';
        $subscriber = $this->service->subscribe(['email' => $email, 'source' => 'footer']);

        $this->assertSame($email, $subscriber->email);
        $this->assertTrue($subscriber->isActive());
        $this->assertTrue($this->service->isSubscribed($email));
    }

    /**
     * 重复订阅不应产生重复记录。
     */
    public function test_duplicate_subscribe_is_idempotent(): void
    {
        $email = 'dup-'.uniqid().'@example.com';
        $this->service->subscribe(['email' => $email]);
        $this->service->subscribe(['email' => $email]);

        $this->assertSame(1, NewsletterSubscriber::where('email', $email)->count());
    }

    /**
     * 退订后再订阅应重新激活。
     */
    public function test_unsubscribe_then_resubscribe_reactivates(): void
    {
        $email = 're-'.uniqid().'@example.com';
        $this->service->subscribe(['email' => $email]);

        $this->assertTrue($this->service->unsubscribe($email));
        $this->assertFalse($this->service->isSubscribed($email));

        $this->service->subscribe(['email' => $email]);
        $this->assertTrue($this->service->isSubscribed($email));
        // 仍只有一条记录
        $this->assertSame(1, NewsletterSubscriber::where('email', $email)->count());
    }
}
