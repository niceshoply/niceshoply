<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Repositories;

use NiceShoply\Common\Models\CustomerPoint;

/**
 * 客户积分账户数据访问层。
 */
class CustomerPointRepo extends BaseRepo
{
    protected string $model = CustomerPoint::class;

    /**
     * 获取或创建积分账户（带行锁，用于并发安全的增减）。
     */
    public function getOrCreateForUpdate(int $customerId): CustomerPoint
    {
        $account = CustomerPoint::query()
            ->where('customer_id', $customerId)
            ->lockForUpdate()
            ->first();

        if ($account) {
            return $account;
        }

        return CustomerPoint::query()->create([
            'customer_id'  => $customerId,
            'balance'      => 0,
            'total_earned' => 0,
            'total_spent'  => 0,
        ]);
    }

    /**
     * 读取账户（无锁）。
     */
    public function findByCustomerId(int $customerId): ?CustomerPoint
    {
        return CustomerPoint::query()->where('customer_id', $customerId)->first();
    }
}
