<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Warehouse;

use Illuminate\Support\Collection;

interface AllocationStrategyInterface
{
    /**
     * Allocate SKUs to warehouses.
     *
     * @param  array  $skuQuantities  [['sku_code' => '...', 'quantity' => N], ...]
     * @param  array  $destAddress  Destination address for distance calculation
     * @param  Collection|null  $warehouses  Pre-filtered warehouses (if null, query all active)
     * @return AllocationResult
     */
    public function allocate(array $skuQuantities, array $destAddress = [], ?Collection $warehouses = null): AllocationResult;
}
