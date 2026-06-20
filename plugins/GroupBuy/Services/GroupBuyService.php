<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GroupBuy\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Plugin\GroupBuy\Models\GroupBuyActivity;
use Plugin\GroupBuy\Models\GroupBuyGroup;
use Plugin\GroupBuy\Models\GroupBuyMember;

class GroupBuyService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 获取生效中的活动。
     */
    public function getActiveActivity(int $activityId): ?GroupBuyActivity
    {
        $now = Carbon::now();

        return GroupBuyActivity::query()
            ->where('id', $activityId)
            ->where('active', true)
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now))
            ->first();
    }

    /**
     * 开团：创建新团并把发起人作为团长。
     *
     * @throws Exception
     */
    public function openGroup(int $activityId, int $customerId): GroupBuyGroup
    {
        $activity = $this->getActiveActivity($activityId);
        if (! $activity) {
            throw new Exception(__('GroupBuy::common.activity_invalid'));
        }
        if ($customerId <= 0) {
            throw new Exception(__('GroupBuy::common.login_required'));
        }

        return DB::transaction(function () use ($activity, $customerId) {
            $group = GroupBuyGroup::query()->create([
                'activity_id'        => $activity->id,
                'leader_customer_id' => $customerId,
                'status'             => 'open',
                'members_count'      => 0,
                'expire_at'          => Carbon::now()->addMinutes($activity->time_limit_minutes),
            ]);

            GroupBuyMember::query()->create([
                'group_id'    => $group->id,
                'customer_id' => $customerId,
                'is_leader'   => true,
            ]);

            return $group;
        });
    }

    /**
     * 参团校验：返回可加入的团。
     *
     * @throws Exception
     */
    public function ensureJoinable(int $groupId, int $customerId): GroupBuyGroup
    {
        $group = GroupBuyGroup::query()->with('activity')->findOrFail($groupId);
        $this->refreshStatus($group);

        if ($group->status !== 'open') {
            throw new Exception(__('GroupBuy::common.group_closed'));
        }
        if ($group->members_count >= ($group->activity->group_size ?? 0)) {
            throw new Exception(__('GroupBuy::common.group_full'));
        }
        if (GroupBuyMember::query()->where('group_id', $groupId)->where('customer_id', $customerId)->exists()) {
            throw new Exception(__('GroupBuy::common.already_joined'));
        }

        return $group;
    }

    /**
     * 团过期检测，过期未成团置为失败。
     */
    public function refreshStatus(GroupBuyGroup $group): void
    {
        if ($group->status === 'open' && $group->expire_at && $group->expire_at->isPast()) {
            $group->update(['status' => 'failed']);
        }
    }

    /**
     * 计算购物车命中拼团活动的抵扣金额。
     */
    public function computeDiscount(array $cartList, int $activityId): float
    {
        $activity = $this->getActiveActivity($activityId);
        if (! $activity) {
            return 0;
        }

        $discount = 0;
        foreach ($cartList as $item) {
            if (! ($item['selected'] ?? true)) {
                continue;
            }
            if ((int) ($item['sku_id'] ?? 0) !== (int) $activity->sku_id) {
                continue;
            }
            $unit = (float) ($item['price'] ?? 0);
            $qty  = (int) ($item['quantity'] ?? 0);
            $diff = $unit - (float) $activity->group_price;
            if ($diff > 0 && $qty > 0) {
                $discount += $diff * $qty;
            }
        }

        return round($discount, 2);
    }

    /**
     * 下单后置：将订单挂到团上，达到人数则成团。
     */
    public function handleOrderConfirmed($order, array $checkout): void
    {
        if (! $order) {
            return;
        }

        $reference  = $checkout['reference'] ?? [];
        $activityId = (int) ($reference['group_buy_activity_id'] ?? 0);
        $groupId    = (int) ($reference['group_buy_group_id'] ?? 0);
        if ($activityId <= 0) {
            return;
        }

        $customerId = (int) ($order->customer_id ?? 0);

        DB::transaction(function () use ($activityId, $groupId, $customerId, $order) {
            $activity = GroupBuyActivity::query()->find($activityId);
            if (! $activity) {
                return;
            }

            // 新开团
            if ($groupId <= 0) {
                $group = GroupBuyGroup::query()->create([
                    'activity_id'        => $activity->id,
                    'leader_customer_id' => $customerId,
                    'status'             => 'open',
                    'members_count'      => 0,
                    'expire_at'          => Carbon::now()->addMinutes($activity->time_limit_minutes),
                ]);
                $isLeader = true;
            } else {
                $group    = GroupBuyGroup::query()->lockForUpdate()->find($groupId);
                $isLeader = false;
                if (! $group || $group->status !== 'open') {
                    return;
                }
            }

            $member = GroupBuyMember::query()->where('group_id', $group->id)->where('customer_id', $customerId)->first();
            if ($member) {
                $member->update(['order_id' => (int) $order->id]);
            } else {
                GroupBuyMember::query()->create([
                    'group_id'    => $group->id,
                    'customer_id' => $customerId,
                    'order_id'    => (int) $order->id,
                    'is_leader'   => $isLeader,
                ]);
            }

            $count = GroupBuyMember::query()->where('group_id', $group->id)->where('order_id', '>', 0)->count();
            $group->members_count = $count;
            if ($count >= $activity->group_size) {
                $group->status = 'success';
            }
            $group->save();
        });
    }
}
