<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Membership\Services;

use Plugin\Membership\Models\CustomerMembership;
use Plugin\Membership\Models\MembershipLevel;

class MembershipService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function getMembership(int $customerId): ?CustomerMembership
    {
        if ($customerId <= 0) {
            return null;
        }

        return CustomerMembership::query()->with('level')->where('customer_id', $customerId)->first();
    }

    /**
     * 返回客户当前等级的折扣百分比（0-100）。
     */
    public function discountPercent(int $customerId): float
    {
        $membership = $this->getMembership($customerId);
        if (! $membership || ! $membership->level || ! $membership->level->active) {
            return 0;
        }

        return (float) $membership->level->discount_percent;
    }

    /**
     * 根据累计消费匹配应得等级。
     */
    public function matchLevel(float $totalSpent): ?MembershipLevel
    {
        return MembershipLevel::query()
            ->where('active', true)
            ->where('min_spent', '<=', $totalSpent)
            ->orderByDesc('min_spent')
            ->first();
    }

    /**
     * 下单后置：累计消费并自动升级。
     */
    public function handleOrderConfirmed($order): void
    {
        if (! $order) {
            return;
        }

        $customerId = (int) ($order->customer_id ?? 0);
        if ($customerId <= 0) {
            return;
        }

        $membership = CustomerMembership::query()->firstOrCreate(
            ['customer_id' => $customerId],
            ['level_id' => 0, 'total_spent' => 0]
        );

        $membership->total_spent += (float) ($order->total ?? 0);

        $level = $this->matchLevel($membership->total_spent);
        if ($level) {
            $membership->level_id = $level->id;
        }

        $membership->save();
    }
}
