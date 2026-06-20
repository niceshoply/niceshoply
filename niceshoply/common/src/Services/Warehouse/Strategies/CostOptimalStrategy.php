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
use NiceShoply\Common\Services\Warehouse\AllocationResult;
use NiceShoply\Common\Services\Warehouse\AllocationStrategyInterface;

class CostOptimalStrategy implements AllocationStrategyInterface
{
    /**
     * Allocate by combining priority and distance for cost optimization.
     * Prefers warehouses that can fulfill the entire order to avoid split shipments.
     */
    public function allocate(array $skuQuantities, array $destAddress = [], ?Collection $warehouses = null): AllocationResult
    {
        $warehouses = $warehouses ?? Warehouse::query()->where('active', true)->get();

        // Score each warehouse: lower is better
        $warehouses = $warehouses->sortBy(function ($warehouse) use ($skuQuantities) {
            $score = $warehouse->priority * 10;

            // Bonus for warehouses that can fulfill all items
            $canFulfillAll = true;
            foreach ($skuQuantities as $item) {
                $stock = $warehouse->stocks()->where('sku_code', $item['sku_code'])->first();
                if (! $stock || $stock->available_quantity < $item['quantity']) {
                    $canFulfillAll = false;
                    break;
                }
            }

            if ($canFulfillAll) {
                $score -= 100; // Strong preference for single-warehouse fulfillment
            }

            return $score;
        })->values();

        return WarehouseAllocator::allocateByOrder($warehouses, $skuQuantities);
    }
}
