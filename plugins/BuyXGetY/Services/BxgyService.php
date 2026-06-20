<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\BuyXGetY\Services;

use Plugin\BuyXGetY\Models\BxgyRule;

class BxgyService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('buy_x_get_y', 'enabled', true);
    }

    /**
     * 计算购物车命中的满件优惠折扣总额。
     *
     * @param  array  $cartList
     * @return array{discount: float, titles: string[]}
     */
    public function matchDiscount(array $cartList): array
    {
        $discount = 0.0;
        $titles   = [];

        if (! $this->enabled()) {
            return ['discount' => 0.0, 'titles' => []];
        }

        // 聚合：product_id => 单价数组(按数量展开为单价列表)
        $unitsByProduct = [];
        foreach ($cartList as $item) {
            $pid = (int) ($item['product_id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            if ($pid <= 0 || $qty <= 0) {
                continue;
            }
            for ($i = 0; $i < $qty; $i++) {
                $unitsByProduct[$pid][] = $price;
            }
        }

        $rules = BxgyRule::query()->where('is_active', true)->get();
        foreach ($rules as $rule) {
            $group = max(1, (int) $rule->buy_qty) + max(1, (int) $rule->get_qty);
            $getQty = max(1, (int) $rule->get_qty);
            $pct = min(100, max(0, (int) $rule->discount_percent));
            if ($pct <= 0) {
                continue;
            }

            $targets = (int) $rule->product_id === 0
                ? array_keys($unitsByProduct)
                : [(int) $rule->product_id];

            $ruleDiscount = 0.0;
            foreach ($targets as $pid) {
                if (empty($unitsByProduct[$pid])) {
                    continue;
                }
                $prices = $unitsByProduct[$pid];
                $qty = count($prices);
                $sets = intdiv($qty, $group);
                if ($sets <= 0) {
                    continue;
                }

                // 优惠件数 = 成组次数 * Y，取最低价的那些单品优惠
                $discountUnits = $sets * $getQty;
                sort($prices); // 升序，最低价优先优惠
                for ($i = 0; $i < $discountUnits && $i < count($prices); $i++) {
                    $ruleDiscount += $prices[$i] * $pct / 100;
                }
            }

            if ($ruleDiscount > 0) {
                $discount += $ruleDiscount;
                $titles[] = $rule->name;
            }
        }

        return ['discount' => round($discount, 2), 'titles' => $titles];
    }
}
