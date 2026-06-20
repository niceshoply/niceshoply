<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CartRule\Services;

use Illuminate\Support\Carbon;
use Plugin\CartRule\Models\CartRule;

class CartRuleService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 返回订单小计可享受的最优满减规则及折扣金额。
     *
     * @return array{rule: ?CartRule, discount: float}
     */
    public function bestDiscount(float $subtotal): array
    {
        $now = Carbon::now();

        $rules = CartRule::query()
            ->where('active', true)
            ->where('min_amount', '<=', $subtotal)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            })
            ->get();

        $best     = null;
        $maxValue = 0;

        foreach ($rules as $rule) {
            $discount = $this->computeDiscount($rule, $subtotal);
            if ($discount > $maxValue) {
                $maxValue = $discount;
                $best     = $rule;
            }
        }

        return ['rule' => $best, 'discount' => round($maxValue, 2)];
    }

    public function computeDiscount(CartRule $rule, float $subtotal): float
    {
        $discount = $rule->discount_type === 'percent'
            ? $subtotal * $rule->discount_value / 100
            : min($rule->discount_value, $subtotal);

        if ($rule->discount_type === 'percent' && $rule->max_discount > 0) {
            $discount = min($discount, $rule->max_discount);
        }

        return round(max($discount, 0), 2);
    }
}
