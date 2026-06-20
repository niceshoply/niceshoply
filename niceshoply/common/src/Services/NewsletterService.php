<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use NiceShoply\Common\Models\NewsletterSubscriber;
use NiceShoply\Common\Repositories\NewsletterRepo;
use Throwable;

/**
 * Newsletter 订阅服务
 */
class NewsletterService
{
    private NewsletterRepo $newsletterRepo;

    public function __construct()
    {
        $this->newsletterRepo = NewsletterRepo::getInstance();
    }

    /**
     * 获取服务实例。
     *
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 订阅邮箱到 Newsletter。
     *
     * @param  array  $data
     * @return NewsletterSubscriber
     * @throws Throwable
     */
    public function subscribe(array $data): NewsletterSubscriber
    {
        // 若已登录则关联客户
        $customer = current_customer();
        if ($customer) {
            $data['customer_id'] = $customer->id;
            if (empty($data['name']) && $customer->name) {
                $data['name'] = $customer->name;
            }
        }

        return $this->newsletterRepo->subscribe($data);
    }

    /**
     * 退订邮箱。
     *
     * @param  string  $email
     * @return bool
     */
    public function unsubscribe(string $email): bool
    {
        return $this->newsletterRepo->unsubscribe($email);
    }

    /**
     * 邮箱是否已订阅。
     *
     * @param  string  $email
     * @return bool
     */
    public function isSubscribed(string $email): bool
    {
        $subscriber = $this->newsletterRepo->findByEmail($email);

        return $subscriber && $subscriber->isActive();
    }

    /**
     * 活跃订阅者数量。
     *
     * @return int
     */
    public function getActiveCount(): int
    {
        return $this->newsletterRepo->getActiveCount();
    }
}
