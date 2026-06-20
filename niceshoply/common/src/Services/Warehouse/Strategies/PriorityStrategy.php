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

class PriorityStrategy implements AllocationStrategyInterface
{
    /**
     * Allocate by warehouse priority (lower priority number = higher priority).
     */
    public function allocate(array $skuQuantities, array $destAddress = [], ?Collection $warehouses = null): AllocationResult
    {
        $warehouses = $warehouses ?? Warehouse::query()->where('active', true)->get();
        $warehouses = $warehouses->sortBy('priority')->values();

        return WarehouseAllocator::allocateByOrder($warehouses, $skuQuantities);
    }
}
