<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bundle\Services;

use Plugin\Bundle\Models\BundleDeal;

class BundleService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('bundle', 'enabled', true);
    }

    /**
     * 计算购物车命中的套装折扣。
     *
     * @param  array  $cartList  CheckoutService::getCartList()
     * @return array{discount: float, titles: string[]}
     */
    public function matchDiscount(array $cartList): array
    {
        $discount = 0.0;
        $titles   = [];

        if (! $this->enabled()) {
            return ['discount' => 0.0, 'titles' => []];
        }

        // 按 product_id 聚合购物车：数量与单价（取最低单价）
        $byProduct = [];
        foreach ($cartList as $item) {
            $pid = (int) ($item['product_id'] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            if (! isset($byProduct[$pid])) {
                $byProduct[$pid] = ['qty' => 0, 'price' => (float) ($item['price'] ?? 0)];
            }
            $byProduct[$pid]['qty'] += (int) ($item['quantity'] ?? 0);
            $byProduct[$pid]['price'] = min($byProduct[$pid]['price'], (float) ($item['price'] ?? 0));
        }

        $deals = BundleDeal::query()->where('is_active', true)->get();
        foreach ($deals as $deal) {
            $items = is_array($deal->items) ? $deal->items : [];
            if (empty($items)) {
                continue;
            }

            // 该套装在购物车中可成套的次数
            $times = PHP_INT_MAX;
            $normalPer = 0.0;
            $ok = true;
            foreach ($items as $it) {
                $pid = (int) ($it['product_id'] ?? 0);
                $need = max(1, (int) ($it['quantity'] ?? 1));
                if ($pid <= 0 || ! isset($byProduct[$pid]) || $byProduct[$pid]['qty'] < $need) {
                    $ok = false;
                    break;
                }
                $times = min($times, intdiv($byProduct[$pid]['qty'], $need));
                $normalPer += $byProduct[$pid]['price'] * $need;
            }

            if (! $ok || $times <= 0 || $times === PHP_INT_MAX) {
                continue;
            }

            $gapPer = $normalPer - (float) $deal->bundle_price;
            if ($gapPer <= 0) {
                continue;
            }

            $discount += $gapPer * $times;
            $titles[] = $deal->name;
        }

        return ['discount' => round($discount, 2), 'titles' => $titles];
    }
}
