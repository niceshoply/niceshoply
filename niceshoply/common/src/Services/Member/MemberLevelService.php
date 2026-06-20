<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Member;

use NiceShoply\Common\Jobs\RecalculateMemberLevelJob;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\MemberLevel;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Repositories\CustomerPointRepo;
use NiceShoply\Common\Repositories\MemberLevelRepo;
use NiceShoply\Common\Services\BaseService;
use NiceShoply\Common\Services\StateMachineService;
use Throwable;

/**
 * 会员等级服务：价格折扣、免运费、升降级重算。
 */
class MemberLevelService extends BaseService
{
    /**
     * 价格 Hook：按当前客户等级注入会员折扣。
     *
     * @param  array{sku: mixed, price: float|int}  $data
     * @return array{sku: mixed, price: float|int}
     */
    public function applyMemberPrice(array $data): array
    {
        $price = (float) ($data['price'] ?? 0);
        if ($price <= 0) {
            return $data;
        }

        $customerId = current_customer_id();
        if ($customerId <= 0) {
            return $data;
        }

        $level = $this->getCustomerLevel($customerId);
        if (! $level || (float) $level->discount_percent <= 0) {
            return $data;
        }

        $discountRate  = min(100, max(0, (float) $level->discount_percent)) / 100;
        $data['price'] = round($price * (1 - $discountRate), currency_decimal_place());

        return $data;
    }

    /**
     * 客户是否享有会员免运费。
     */
    public function customerHasFreeShipping(int $customerId): bool
    {
        if ($customerId <= 0) {
            return false;
        }

        $level = $this->getCustomerLevel($customerId);

        return $level && $level->free_shipping;
    }

    /**
     * 读取客户当前等级模型。
     */
    public function getCustomerLevel(int $customerId): ?MemberLevel
    {
        $customer = Customer::query()->find($customerId);
        if (! $customer || ! $customer->member_level_id) {
            return null;
        }

        $level = MemberLevel::query()
            ->where('id', $customer->member_level_id)
            ->where('active', true)
            ->first();

        return $level;
    }

    /**
     * 异步队列重算客户等级。
     */
    public function dispatchRecalculate(int $customerId): void
    {
        if ($customerId <= 0) {
            return;
        }

        RecalculateMemberLevelJob::dispatch($customerId);
    }

    /**
     * 同步重算并更新 customers.member_level_id。
     *
     * @throws Throwable
     */
    public function recalculateForCustomer(int $customerId): ?MemberLevel
    {
        if ($customerId <= 0) {
            return null;
        }

        $metricValue = $this->resolveCustomerMetric($customerId);
        $matched     = $this->resolveLevelForMetric($metricValue);

        Customer::query()->where('id', $customerId)->update([
            'member_level_id' => $matched?->id ?? 0,
        ]);

        return $matched;
    }

    /**
     * 按门槛类型读取客户累计指标。
     */
    private function resolveCustomerMetric(int $customerId): float
    {
        $levels = MemberLevelRepo::getInstance()->getActiveLevels();
        if ($levels->isEmpty()) {
            return 0;
        }

        // 若存在 points 类型等级，优先用积分累计；否则用消费金额
        $usesPoints = $levels->contains(fn (MemberLevel $level) => $level->threshold_type === 'points');
        if ($usesPoints) {
            $account = CustomerPointRepo::getInstance()->findByCustomerId($customerId);

            return (float) ($account->total_earned ?? 0);
        }

        return (float) Order::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', [
                StateMachineService::PAID,
                StateMachineService::PARTIALLY_SHIPPED,
                StateMachineService::SHIPPED,
                StateMachineService::COMPLETED,
            ])
            ->sum('total');
    }

    /**
     * 在启用等级中匹配最高可达等级（priority 最大且门槛满足）。
     */
    private function resolveLevelForMetric(float $metricValue): ?MemberLevel
    {
        $levels  = MemberLevelRepo::getInstance()->getActiveLevels();
        $matched = null;

        foreach ($levels as $level) {
            if ($metricValue >= (float) $level->threshold_value) {
                if (! $matched || $level->priority > $matched->priority) {
                    $matched = $level;
                }
            }
        }

        return $matched;
    }
}
