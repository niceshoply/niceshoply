<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories\Warehouse;

use Illuminate\Database\Eloquent\Builder;
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Repositories\BaseRepo;

class StockRepo extends BaseRepo
{
    protected string $model = Stock::class;

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Stock::query()->with(['warehouse', 'sku']);
        $filters = array_merge($this->filters, $filters);

        $warehouseId = $filters['warehouse_id'] ?? 0;
        if ($warehouseId) {
            $builder->where('warehouse_id', $warehouseId);
        }

        $skuCode = $filters['sku_code'] ?? '';
        if ($skuCode) {
            $builder->where('sku_code', 'like', "%{$skuCode}%");
        }

        $productId = $filters['product_id'] ?? 0;
        if ($productId) {
            $builder->where('product_id', $productId);
        }

        $lowStock = $filters['low_stock'] ?? false;
        if ($lowStock) {
            $builder->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->where('low_stock_threshold', '>', 0);
        }

        return $builder;
    }

    /**
     * Get or create a warehouse stock record.
     *
     * @param  int  $warehouseId
     * @param  string  $skuCode
     * @param  int  $skuId
     * @param  int  $productId
     * @return Stock
     */
    public function getOrCreate(int $warehouseId, string $skuCode, int $skuId = 0, int $productId = 0): Stock
    {
        return Stock::query()->firstOrCreate(
            ['warehouse_id' => $warehouseId, 'sku_code' => $skuCode],
            ['sku_id' => $skuId, 'product_id' => $productId, 'quantity' => 0, 'reserved_quantity' => 0]
        );
    }

    /**
     * Get total available quantity across all warehouses.
     *
     * @param  string  $skuCode
     * @return int
     */
    public function getTotalAvailable(string $skuCode): int
    {
        return (int) Stock::query()
            ->where('sku_code', $skuCode)
            ->whereHas('warehouse', fn ($q) => $q->where('active', true))
            ->selectRaw('SUM(quantity - reserved_quantity) as total')
            ->value('total');
    }
}
