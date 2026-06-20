<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Wholesale\Services;

use Plugin\Wholesale\Models\WholesaleTier;

class WholesaleService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 指定 SKU 在给定数量下命中的最优阶梯单价（无命中返回 null）。
     */
    public function bestTierPrice(int $skuId, int $qty): ?float
    {
        if ($skuId <= 0 || $qty <= 0) {
            return null;
        }

        /** @var WholesaleTier|null $tier */
        $tier = WholesaleTier::query()
            ->where('sku_id', $skuId)
            ->where('is_active', true)
            ->where('min_qty', '<=', $qty)
            ->orderByDesc('min_qty')
            ->orderBy('price')
            ->first();

        return $tier ? (float) $tier->price : null;
    }

    /**
     * 购物车命中阶梯价的抵扣总额。
     */
    public function computeDiscount(array $cartList): float
    {
        $discount = 0;

        foreach ($cartList as $item) {
            if (! ($item['selected'] ?? true)) {
                continue;
            }
            $skuId = (int) ($item['sku_id'] ?? 0);
            $unit  = (float) ($item['price'] ?? 0);
            $qty   = (int) ($item['quantity'] ?? 0);
            if ($skuId <= 0 || $qty <= 0) {
                continue;
            }

            $tierPrice = $this->bestTierPrice($skuId, $qty);
            if ($tierPrice === null) {
                continue;
            }

            $diff = $unit - $tierPrice;
            if ($diff > 0) {
                $discount += $diff * $qty;
            }
        }

        return round($discount, 2);
    }

    /**
     * 指定 SKU 的阶梯价表（用于商详页展示）。
     */
    public function tiersForSku(int $skuId): array
    {
        return WholesaleTier::query()
            ->where('sku_id', $skuId)
            ->where('is_active', true)
            ->orderBy('min_qty')
            ->get(['min_qty', 'price'])
            ->toArray();
    }
}
