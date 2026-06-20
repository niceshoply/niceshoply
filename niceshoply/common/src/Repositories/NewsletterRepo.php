<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use NiceShoply\Common\Models\NewsletterSubscriber;
use Throwable;

/**
 * Newsletter 订阅者仓库
 */
class NewsletterRepo extends BaseRepo
{
    protected string $model = NewsletterSubscriber::class;

    /**
     * 按筛选条件构造查询。
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = NewsletterSubscriber::query()->with(['customer']);

        $email = $filters['email'] ?? '';
        if ($email) {
            $builder->where('email', 'like', "%{$email}%");
        }

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', "%{$name}%");
        }

        $status = $filters['status'] ?? '';
        if ($status) {
            $builder->where('status', $status);
        }

        $source = $filters['source'] ?? '';
        if ($source) {
            $builder->where('source', $source);
        }

        return fire_hook_filter('common.repo.newsletter.builder', $builder);
    }

    /**
     * 按邮箱查找订阅者。
     *
     * @param  string  $email
     * @return NewsletterSubscriber|null
     */
    public function findByEmail(string $email): ?NewsletterSubscriber
    {
        return NewsletterSubscriber::where('email', $email)->first();
    }

    /**
     * 订阅邮箱。
     *
     * @param  array  $data
     * @return NewsletterSubscriber
     * @throws Throwable
     */
    public function subscribe(array $data): NewsletterSubscriber
    {
        $subscriber = $this->findByEmail($data['email']);

        if ($subscriber) {
            // 已存在：若此前退订则重新激活
            if ($subscriber->status === NewsletterSubscriber::STATUS_UNSUBSCRIBED) {
                $subscriber->subscribe();
                if (isset($data['name'])) {
                    $subscriber->name = $data['name'];
                }
                if (isset($data['source'])) {
                    $subscriber->source = $data['source'];
                }
                if (isset($data['customer_id'])) {
                    $subscriber->customer_id = $data['customer_id'];
                }
                $subscriber->save();
            }
        } else {
            $subscriber = NewsletterSubscriber::create([
                'email'         => $data['email'],
                'name'          => $data['name'] ?? null,
                'customer_id'   => $data['customer_id'] ?? null,
                'status'        => NewsletterSubscriber::STATUS_ACTIVE,
                'source'        => $data['source'] ?? NewsletterSubscriber::SOURCE_FOOTER,
                'subscribed_at' => now(),
            ]);
        }

        return $subscriber;
    }

    /**
     * 退订邮箱。
     *
     * @param  string  $email
     * @return bool
     */
    public function unsubscribe(string $email): bool
    {
        $subscriber = $this->findByEmail($email);

        if ($subscriber && $subscriber->isActive()) {
            $subscriber->unsubscribe();

            return true;
        }

        return false;
    }

    /**
     * 活跃订阅者数量。
     *
     * @return int
     */
    public function getActiveCount(): int
    {
        return NewsletterSubscriber::where('status', NewsletterSubscriber::STATUS_ACTIVE)->count();
    }

    /**
     * 按状态获取订阅者。
     *
     * @param  string  $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection
    {
        return NewsletterSubscriber::where('status', $status)->get();
    }
}
