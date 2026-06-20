<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bargain\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Plugin\Bargain\Models\BargainActivity;
use Plugin\Bargain\Models\BargainRecord;
use Plugin\Bargain\Models\BargainTask;

class BargainService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function getActiveActivity(int $activityId): ?BargainActivity
    {
        $now = Carbon::now();

        return BargainActivity::query()
            ->where('id', $activityId)
            ->where('active', true)
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now))
            ->first();
    }

    /**
     * 发起砍价任务。origin 优先用活动配置，否则用传入的 SKU 当前价。
     *
     * @throws Exception
     */
    public function startTask(int $activityId, int $customerId, float $skuPrice = 0): BargainTask
    {
        $activity = $this->getActiveActivity($activityId);
        if (! $activity) {
            throw new Exception(__('Bargain::common.activity_invalid'));
        }
        if ($customerId <= 0) {
            throw new Exception(__('Bargain::common.login_required'));
        }

        // 同一活动每人仅允许一个进行中的任务
        $existing = BargainTask::query()
            ->where('activity_id', $activityId)
            ->where('customer_id', $customerId)
            ->whereIn('status', ['cutting', 'done'])
            ->first();
        if ($existing) {
            return $existing;
        }

        $origin = $activity->origin_price > 0 ? $activity->origin_price : $skuPrice;
        if ($origin <= 0) {
            throw new Exception(__('Bargain::common.invalid_origin'));
        }

        return BargainTask::query()->create([
            'activity_id'   => $activity->id,
            'customer_id'   => $customerId,
            'origin_price'  => $origin,
            'floor_price'   => $activity->floor_price,
            'current_price' => $origin,
            'status'        => 'cutting',
            'expire_at'     => Carbon::now()->addMinutes($activity->time_limit_minutes),
        ]);
    }

    /**
     * 助力砍价：随机砍掉 [min_cut, max_cut]，不低于底价。
     *
     * @throws Exception
     */
    public function cut(int $taskId, int $helperCustomerId): array
    {
        return DB::transaction(function () use ($taskId, $helperCustomerId) {
            $task = BargainTask::query()->with('activity')->lockForUpdate()->findOrFail($taskId);
            $this->refreshStatus($task);

            if ($task->status !== 'cutting') {
                throw new Exception(__('Bargain::common.task_closed'));
            }

            // 每位助力者仅可砍一次
            if ($helperCustomerId > 0 && BargainRecord::query()->where('task_id', $taskId)->where('helper_customer_id', $helperCustomerId)->exists()) {
                throw new Exception(__('Bargain::common.already_cut'));
            }

            $activity = $task->activity;
            $min      = max($activity->min_cut, 0.01);
            $max      = max($activity->max_cut, $min);

            $remaining = round($task->current_price - $task->floor_price, 2);
            if ($remaining <= 0) {
                $task->update(['status' => 'done', 'current_price' => $task->floor_price]);

                return ['cut_amount' => 0, 'current_price' => $task->floor_price, 'status' => 'done'];
            }

            $cut = round(mt_rand((int) ($min * 100), (int) ($max * 100)) / 100, 2);
            $cut = min($cut, $remaining);

            $task->current_price = round($task->current_price - $cut, 2);
            if ($task->current_price <= $task->floor_price) {
                $task->current_price = $task->floor_price;
                $task->status        = 'done';
            }
            $task->save();

            BargainRecord::query()->create([
                'task_id'            => $taskId,
                'helper_customer_id' => $helperCustomerId,
                'cut_amount'         => $cut,
            ]);

            return ['cut_amount' => $cut, 'current_price' => $task->current_price, 'status' => $task->status];
        });
    }

    public function refreshStatus(BargainTask $task): void
    {
        if ($task->status === 'cutting' && $task->expire_at && $task->expire_at->isPast()) {
            $task->update(['status' => 'expired']);
        }
    }

    /**
     * 计算购物车命中砍价任务的抵扣金额。
     */
    public function computeDiscount(array $cartList, int $taskId, int $customerId): float
    {
        $task = BargainTask::query()->with('activity')->find($taskId);
        if (! $task || $task->customer_id !== $customerId || $task->status !== 'done') {
            return 0;
        }

        $skuId = (int) ($task->activity->sku_id ?? 0);
        foreach ($cartList as $item) {
            if (! ($item['selected'] ?? true)) {
                continue;
            }
            if ((int) ($item['sku_id'] ?? 0) !== $skuId) {
                continue;
            }
            $unit = (float) ($item['price'] ?? 0);
            $diff = $unit - (float) $task->current_price;
            // 砍价价仅作用于 1 件
            if ($diff > 0) {
                return round($diff, 2);
            }
        }

        return 0;
    }

    /**
     * 下单后置：标记任务已使用。
     */
    public function handleOrderConfirmed($order, array $checkout): void
    {
        if (! $order) {
            return;
        }

        $reference = $checkout['reference'] ?? [];
        $taskId    = (int) ($reference['bargain_task_id'] ?? 0);
        if ($taskId <= 0) {
            return;
        }

        $task = BargainTask::query()->find($taskId);
        if ($task && $task->status === 'done' && $task->customer_id === (int) ($order->customer_id ?? 0)) {
            $task->update(['status' => 'used', 'order_id' => (int) $order->id]);
        }
    }
}
