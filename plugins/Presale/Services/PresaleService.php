<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Presale\Services;

use Illuminate\Support\Carbon;
use Plugin\Presale\Models\PresaleActivity;
use Plugin\Presale\Models\PresaleItem;

class PresaleService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * 当前生效的预售项，返回 [sku_id => PresaleItem]。
     */
    public function activeItems(): array
    {
        $now = Carbon::now();

        $ids = PresaleActivity::query()
            ->where('active', true)
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now))
            ->pluck('id');

        if ($ids->isEmpty()) {
            return [];
        }

        $map = [];
        foreach (PresaleItem::query()->whereIn('presale_id', $ids)->get() as $item) {
            if ($item->qty_limit > 0 && $item->sold >= $item->qty_limit) {
                continue;
            }
            if (! isset($map[$item->sku_id]) || $item->presale_price < $map[$item->sku_id]->presale_price) {
                $map[$item->sku_id] = $item;
            }
        }

        return $map;
    }

    /**
     * 购物车命中预售的抵扣：预售价差额 + 定金膨胀(expand-deposit)。
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

            $unit = (float) ($item['price'] ?? 0);
            $qty  = (int) ($item['quantity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }

            $presale = $active[$skuId];
            $priceDiff = max($unit - (float) $presale->presale_price, 0);
            $expandBonus = max((float) $presale->expand - (float) $presale->deposit, 0);

            $discount += ($priceDiff + $expandBonus) * $qty;
        }

        return round($discount, 2);
    }

    /**
     * 预售信息（用于商详页展示）。
     */
    public function infoForSku(int $skuId): ?array
    {
        $active = $this->activeItems();
        if (! isset($active[$skuId])) {
            return null;
        }

        $item     = $active[$skuId];
        $activity = PresaleActivity::query()->find($item->presale_id);

        return [
            'presale_price' => (float) $item->presale_price,
            'deposit'       => (float) $item->deposit,
            'expand'        => (float) $item->expand,
            'ship_date'     => $activity?->ship_date?->toDateString(),
            'end_at'        => $activity?->end_at?->toDateTimeString(),
        ];
    }

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
