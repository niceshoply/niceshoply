<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Plugin\Distribution\Models\DistributionCommission;
use Plugin\Distribution\Models\DistributionRelation;
use Plugin\Distribution\Models\Distributor;

class DistributionService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 成为推广员（幂等）。可选绑定上级推广员邀请码。
     *
     * @throws Exception
     */
    public function becomeDistributor(int $customerId, string $parentCode = ''): Distributor
    {
        if ($customerId <= 0) {
            throw new Exception(__('Distribution::common.login_required'));
        }

        $distributor = Distributor::query()->where('customer_id', $customerId)->first();
        if ($distributor) {
            return $distributor;
        }

        $parentId = 0;
        if ($parentCode !== '') {
            $parent = Distributor::query()->where('code', $parentCode)->where('active', true)->first();
            if ($parent && $parent->customer_id !== $customerId) {
                $parentId = (int) $parent->customer_id;
            }
        }

        return Distributor::query()->create([
            'customer_id' => $customerId,
            'code'        => $this->generateCode(),
            'parent_id'   => $parentId,
            'active'      => true,
        ]);
    }

    protected function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Distributor::query()->where('code', $code)->exists());

        return $code;
    }

    /**
     * 绑定买家与推广员关系（一次性，先到先得）。
     */
    public function bindReferral(int $customerId, string $code): bool
    {
        if ($customerId <= 0 || $code === '') {
            return false;
        }

        if (DistributionRelation::query()->where('customer_id', $customerId)->exists()) {
            return false;
        }

        $distributor = Distributor::query()->where('code', $code)->where('active', true)->first();
        if (! $distributor || $distributor->customer_id === $customerId) {
            return false;
        }

        DistributionRelation::query()->create([
            'customer_id'    => $customerId,
            'distributor_id' => $distributor->customer_id,
        ]);

        return true;
    }

    /**
     * 下单后置：为买家上级推广员生成佣金（pending）。
     */
    public function handleOrderConfirmed($order): void
    {
        if (! $order) {
            return;
        }

        $buyerId = (int) ($order->customer_id ?? 0);
        if ($buyerId <= 0) {
            return;
        }

        $relation = DistributionRelation::query()->where('customer_id', $buyerId)->first();
        if (! $relation) {
            return;
        }

        $base = plugin_setting('distribution', 'commission_base', 'subtotal') === 'total'
            ? (float) ($order->total ?? 0)
            : (float) ($order->subtotal ?? $order->total ?? 0);
        if ($base <= 0) {
            return;
        }

        $rates = [
            1 => (float) plugin_setting('distribution', 'level1_rate', 0),
            2 => (float) plugin_setting('distribution', 'level2_rate', 0),
        ];

        // level 1：直接推广员；level 2：推广员的上级
        $level1 = Distributor::query()->where('customer_id', $relation->distributor_id)->where('active', true)->first();
        if (! $level1) {
            return;
        }

        $this->createCommission($order->id, $buyerId, (int) $level1->customer_id, 1, $base, $rates[1]);

        if ($rates[2] > 0 && $level1->parent_id > 0) {
            $level2 = Distributor::query()->where('customer_id', $level1->parent_id)->where('active', true)->first();
            if ($level2) {
                $this->createCommission($order->id, $buyerId, (int) $level2->customer_id, 2, $base, $rates[2]);
            }
        }
    }

    protected function createCommission(int $orderId, int $buyerId, int $distributorCustomerId, int $level, float $base, float $rate): void
    {
        if ($rate <= 0) {
            return;
        }
        $amount = round($base * $rate / 100, 2);
        if ($amount <= 0) {
            return;
        }

        DistributionCommission::query()->create([
            'order_id'                => $orderId,
            'buyer_customer_id'       => $buyerId,
            'distributor_customer_id' => $distributorCustomerId,
            'level'                   => $level,
            'base_amount'             => $base,
            'rate'                    => $rate,
            'amount'                  => $amount,
            'status'                  => 'pending',
        ]);

        Distributor::query()->where('customer_id', $distributorCustomerId)->increment('total_commission', $amount);
    }

    /**
     * 结算佣金：入账到推广员会员余额。
     *
     * @throws Exception
     */
    public function settle(int $commissionId): void
    {
        DB::transaction(function () use ($commissionId) {
            $commission = DistributionCommission::query()->lockForUpdate()->findOrFail($commissionId);
            if ($commission->status !== 'pending') {
                throw new Exception(__('Distribution::common.already_settled'));
            }

            $commission->update(['status' => 'settled']);

            Distributor::query()->where('customer_id', $commission->distributor_customer_id)
                ->increment('settled_commission', $commission->amount);

            // 入账到会员余额（customers.balance）
            DB::table('customers')->where('id', $commission->distributor_customer_id)->increment('balance', $commission->amount);
        });
    }
}
