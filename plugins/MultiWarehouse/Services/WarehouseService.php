<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MultiWarehouse\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Models\Product\Sku;
use Plugin\MultiWarehouse\Models\Warehouse;
use Plugin\MultiWarehouse\Models\WarehouseStock;
use Plugin\MultiWarehouse\Models\WarehouseTransfer;

class WarehouseService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function enabled(): bool
    {
        return (bool) plugin_setting('multi_warehouse', 'enabled', true);
    }

    public function defaultWarehouse(): ?Warehouse
    {
        return Warehouse::query()->where('is_active', true)->where('is_default', true)->first()
            ?? Warehouse::query()->where('is_active', true)->orderBy('id')->first();
    }

    /**
     * 设置/更新分仓库存。
     */
    public function setStock(int $warehouseId, int $skuId, int $quantity): void
    {
        WarehouseStock::query()->updateOrCreate(
            ['warehouse_id' => $warehouseId, 'sku_id' => $skuId],
            ['quantity' => max(0, $quantity)]
        );

        if ((bool) plugin_setting('multi_warehouse', 'sync_sku_total', true)) {
            $this->syncSkuTotal($skuId);
        }
    }

    /**
     * 汇总分仓库存到 SKU 总库存。
     */
    public function syncSkuTotal(int $skuId): void
    {
        $total = (int) WarehouseStock::query()->where('sku_id', $skuId)->sum('quantity');
        Sku::query()->whereKey($skuId)->update(['quantity' => $total]);
    }

    public function syncAll(): int
    {
        $skuIds = WarehouseStock::query()->distinct()->pluck('sku_id');
        foreach ($skuIds as $skuId) {
            $this->syncSkuTotal((int) $skuId);
        }

        return $skuIds->count();
    }

    /**
     * 仓间调拨。
     *
     * @throws Exception
     */
    public function transfer(int $fromId, int $toId, int $skuId, int $qty, string $remark = ''): void
    {
        if ($fromId === $toId || $qty <= 0) {
            throw new Exception(__('MultiWarehouse::common.invalid_transfer'));
        }

        DB::transaction(function () use ($fromId, $toId, $skuId, $qty, $remark) {
            $from = WarehouseStock::query()->lockForUpdate()->firstOrCreate(
                ['warehouse_id' => $fromId, 'sku_id' => $skuId],
                ['quantity' => 0]
            );
            if ($from->quantity < $qty) {
                throw new Exception(__('MultiWarehouse::common.insufficient_stock'));
            }
            $from->decrement('quantity', $qty);

            $to = WarehouseStock::query()->lockForUpdate()->firstOrCreate(
                ['warehouse_id' => $toId, 'sku_id' => $skuId],
                ['quantity' => 0]
            );
            $to->increment('quantity', $qty);

            WarehouseTransfer::query()->create([
                'from_warehouse_id' => $fromId,
                'to_warehouse_id'   => $toId,
                'sku_id'            => $skuId,
                'quantity'          => $qty,
                'remark'            => $remark,
                'created_at'        => now(),
            ]);

            if ((bool) plugin_setting('multi_warehouse', 'sync_sku_total', true)) {
                $this->syncSkuTotal($skuId);
            }
        });
    }

    /**
     * 推荐发货仓：优先同城/同省，否则库存最多。
     */
    public function allocate(int $skuId, int $qty, ?string $province = null, ?string $city = null): ?array
    {
        $stocks = WarehouseStock::query()
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_stocks.warehouse_id')
            ->where('warehouse_stocks.sku_id', $skuId)
            ->where('warehouse_stocks.quantity', '>=', $qty)
            ->where('warehouses.is_active', true)
            ->select('warehouses.*', 'warehouse_stocks.quantity as stock_qty')
            ->get();

        if ($stocks->isEmpty()) {
            return null;
        }

        $picked = null;
        if ($province) {
            $picked = $stocks->first(fn ($w) => $w->province === $province && ($city === null || $w->city === $city))
                ?? $stocks->first(fn ($w) => $w->province === $province);
        }

        $picked = $picked ?? $stocks->sortByDesc('stock_qty')->first();

        return [
            'warehouse_id'   => $picked->id,
            'warehouse_name' => $picked->name,
            'warehouse_code' => $picked->code,
            'stock'          => (int) $picked->stock_qty,
        ];
    }

    public function stockBySku(int $skuId): array
    {
        return WarehouseStock::query()
            ->join('warehouses', 'warehouses.id', '=', 'warehouse_stocks.warehouse_id')
            ->where('warehouse_stocks.sku_id', $skuId)
            ->where('warehouses.is_active', true)
            ->select('warehouses.id', 'warehouses.name', 'warehouses.code', 'warehouse_stocks.quantity')
            ->get()
            ->map(fn ($r) => [
                'warehouse_id'   => $r->id,
                'warehouse_name' => $r->name,
                'warehouse_code' => $r->code,
                'quantity'       => (int) $r->quantity,
            ])
            ->all();
    }
}
