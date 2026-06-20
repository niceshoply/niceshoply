<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Warehouse\Strategies;

use Illuminate\Support\Collection;
use NiceShoply\Common\Models\Warehouse;
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Services\Warehouse\AllocationResult;
use NiceShoply\Common\Services\Warehouse\AllocationStrategyInterface;

class StockFirstStrategy implements AllocationStrategyInterface
{
    /**
     * Allocate by available stock quantity (highest stock first).
     */
    public function allocate(array $skuQuantities, array $destAddress = [], ?Collection $warehouses = null): AllocationResult
    {
        $warehouses = $warehouses ?? Warehouse::query()->where('active', true)->get();

        // Sort warehouses by total available stock descending
        $warehouses = $warehouses->sortByDesc(function ($warehouse) use ($skuQuantities) {
            $totalAvailable = 0;
            foreach ($skuQuantities as $item) {
                $stock = Stock::query()
                    ->where('warehouse_id', $warehouse->id)
                    ->where('sku_code', $item['sku_code'])
                    ->first();
                if ($stock) {
                    $totalAvailable += $stock->available_quantity;
                }
            }

            return $totalAvailable;
        })->values();

        return WarehouseAllocator::allocateByOrder($warehouses, $skuQuantities);
    }
}
