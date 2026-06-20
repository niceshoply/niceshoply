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
use NiceShoply\Common\Models\Warehouse\Stock;
use NiceShoply\Common\Services\Warehouse\AllocationResult;

class WarehouseAllocator
{
    /**
     * Allocate SKUs to warehouses in the given order.
     * Tries to fulfill from each warehouse in sequence, splitting across warehouses if needed.
     *
     * @param  Collection  $warehouses  Ordered collection of Warehouse models
     * @param  array  $skuQuantities  [['sku_code' => '...', 'quantity' => N], ...]
     * @return AllocationResult
     */
    public static function allocateByOrder(Collection $warehouses, array $skuQuantities): AllocationResult
    {
        $allocations      = [];
        $warehouseGroups  = [];
        $insufficientSkus = [];

        // Track remaining quantity needed for each SKU
        $remaining = [];
        foreach ($skuQuantities as $item) {
            $remaining[$item['sku_code']] = $item['quantity'];
        }

        foreach ($warehouses as $warehouse) {
            $warehouseItems = [];

            foreach ($remaining as $skuCode => $neededQty) {
                if ($neededQty <= 0) {
                    continue;
                }

                $stock = Stock::query()
                    ->where('warehouse_id', $warehouse->id)
                    ->where('sku_code', $skuCode)
                    ->first();

                $available = $stock ? $stock->available_quantity : 0;
                if ($available <= 0) {
                    continue;
                }

                $allocateQty      = min($neededQty, $available);
                $warehouseItems[] = [
                    'sku_code' => $skuCode,
                    'quantity' => $allocateQty,
                ];
                $remaining[$skuCode] -= $allocateQty;
            }

            if (! empty($warehouseItems)) {
                $allocations[$warehouse->id]     = $warehouseItems;
                $warehouseGroups[$warehouse->id] = $warehouse;
            }

            // Check if all fulfilled
            if (array_sum($remaining) <= 0) {
                break;
            }
        }

        // Collect insufficient SKUs
        foreach ($remaining as $skuCode => $qty) {
            if ($qty > 0) {
                $insufficientSkus[] = $skuCode;
            }
        }

        return new AllocationResult(
            allocations: $allocations,
            isSplit: count($allocations) > 1,
            warehouseGroups: $warehouseGroups,
            insufficientSkus: $insufficientSkus,
        );
    }
}
