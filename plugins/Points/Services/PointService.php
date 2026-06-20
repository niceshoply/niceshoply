<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Points\Services;

use Illuminate\Support\Facades\DB;
use Plugin\Points\Models\PointAccount;
use Plugin\Points\Models\PointLog;

class PointService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function balance(int $customerId): int
    {
        if ($customerId <= 0) {
            return 0;
        }

        return (int) (PointAccount::query()->where('customer_id', $customerId)->value('balance') ?? 0);
    }

    /**
     * 变更积分（正数获取/负数消耗），写入流水。返回变更后余额。
     */
    public function change(int $customerId, int $change, string $type = 'adjust', int $orderId = 0, string $remark = ''): int
    {
        if ($customerId <= 0 || $change === 0) {
            return $this->balance($customerId);
        }

        return DB::transaction(function () use ($customerId, $change, $type, $orderId, $remark) {
            $account = PointAccount::query()->lockForUpdate()->firstOrCreate(
                ['customer_id' => $customerId],
                ['balance' => 0, 'total_earned' => 0, 'total_spent' => 0]
            );

            $newBalance = max($account->balance + $change, 0);
            $account->balance = $newBalance;
            if ($change > 0) {
                $account->total_earned += $change;
            } else {
                $account->total_spent += abs($change);
            }
            $account->save();

            PointLog::query()->create([
                'customer_id'   => $customerId,
                'change'        => $change,
                'balance_after' => $newBalance,
                'type'          => $type,
                'order_id'      => $orderId,
                'remark'        => $remark,
            ]);

            return $newBalance;
        });
    }

    /**
     * 下单后置：扣除已用积分、按实付赠送积分。
     */
    public function handleOrderConfirmed($order, array $checkout): void
    {
        if (! $order) {
            return;
        }

        $customerId = (int) ($order->customer_id ?? 0);
        if ($customerId <= 0) {
            return;
        }

        // 1) 扣除本单使用的积分
        $reference = $checkout['reference'] ?? [];
        $usePoints = (int) ($reference['use_points'] ?? 0);
        if ($usePoints > 0) {
            $usePoints = min($usePoints, $this->balance($customerId));
            if ($usePoints > 0) {
                $this->change($customerId, -$usePoints, 'redeem', (int) ($order->id ?? 0), __('Points::common.log_redeem'));
            }
        }

        // 2) 按订单实付赠送积分
        $earnPerUnit = (float) plugin_setting('points', 'earn_per_unit', 0);
        if ($earnPerUnit > 0) {
            $amount = (float) ($order->total ?? 0);
            $earn   = (int) floor($amount * $earnPerUnit);
            if ($earn > 0) {
                $this->change($customerId, $earn, 'order', (int) ($order->id ?? 0), __('Points::common.log_earn'));
            }
        }
    }

    /**
     * 计算积分抵现金额（货币），并返回实际使用积分数。
     *
     * @return array{discount: float, points: int}
     */
    public function computeRedeem(int $customerId, int $requestPoints, float $subtotal): array
    {
        if ($requestPoints <= 0 || $subtotal <= 0) {
            return ['discount' => 0, 'points' => 0];
        }

        $pointsPerUnit = (float) plugin_setting('points', 'points_per_unit', 0);
        if ($pointsPerUnit <= 0) {
            return ['discount' => 0, 'points' => 0];
        }

        $available     = $this->balance($customerId);
        $usablePoints  = min($requestPoints, $available);

        // 单笔抵扣上限
        $maxRatio     = (float) plugin_setting('points', 'max_redeem_ratio', 100);
        $maxDiscount  = $subtotal * max(min($maxRatio, 100), 0) / 100;
        $maxPoints    = (int) floor($maxDiscount * $pointsPerUnit);
        if ($maxPoints > 0) {
            $usablePoints = min($usablePoints, $maxPoints);
        }

        $discount = round($usablePoints / $pointsPerUnit, 2);

        return ['discount' => $discount, 'points' => $usablePoints];
    }
}
