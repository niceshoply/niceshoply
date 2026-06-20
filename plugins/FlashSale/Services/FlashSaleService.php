<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FlashSale\Services;

use Illuminate\Support\Carbon;
use Plugin\FlashSale\Models\FlashSale;
use Plugin\FlashSale\Models\FlashSaleItem;

class FlashSaleService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 当前生效的秒杀项，返回 [sku_id => FlashSaleItem]。
     */
    public function activeItems(): array
    {
        $now = Carbon::now();

        $saleIds = FlashSale::query()
            ->where('active', true)
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now))
            ->pluck('id');

        if ($saleIds->isEmpty()) {
            return [];
        }

        $items = FlashSaleItem::query()
            ->whereIn('flash_sale_id', $saleIds)
            ->get();

        $map = [];
        foreach ($items as $item) {
            // 限量已售罄则跳过
            if ($item->qty_limit > 0 && $item->sold >= $item->qty_limit) {
                continue;
            }
            // 同一 SKU 命中多条时取最低价
            if (! isset($map[$item->sku_id]) || $item->sale_price < $map[$item->sku_id]->sale_price) {
                $map[$item->sku_id] = $item;
            }
        }

        return $map;
    }

    /**
     * 计算购物车命中的秒杀总抵扣金额。
     */
    public function computeDiscount(array $cartList): float
    {
        $active = $this->activeItems();
        if (empty($active)) {
            return 0;
        }

        $discount = 0;
        foreach ($cartList as $item) {
            if (! ($item['selected'] ?? true)) {
                continue;
            }
            $skuId = (int) ($item['sku_id'] ?? 0);
            if (! isset($active[$skuId])) {
                continue;
            }

            $unit     = (float) ($item['price'] ?? 0);
            $qty      = (int) ($item['quantity'] ?? 0);
            $salePrice = (float) $active[$skuId]->sale_price;
            $diff     = $unit - $salePrice;
            if ($diff > 0 && $qty > 0) {
                $discount += $diff * $qty;
            }
        }

        return round($discount, 2);
    }

    /**
     * 下单后置：累计已售数量。
     */
    public function handleOrderConfirmed(array $cartList): void
    {
        $active = $this->activeItems();
        if (empty($active)) {
            return;
        }

        foreach ($cartList as $item) {
            $skuId = (int) ($item['sku_id'] ?? 0);
            if (isset($active[$skuId])) {
                $active[$skuId]->increment('sold', (int) ($item['quantity'] ?? 0));
            }
        }
    }
}
