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

class NearestStrategy implements AllocationStrategyInterface
{
    /**
     * Allocate by geographic distance to destination address.
     */
    public function allocate(array $skuQuantities, array $destAddress = [], ?Collection $warehouses = null): AllocationResult
    {
        $warehouses = $warehouses ?? Warehouse::query()->where('active', true)->get();

        $destLat = $destAddress['latitude'] ?? 0;
        $destLng = $destAddress['longitude'] ?? 0;

        if ($destLat && $destLng) {
            $warehouses = $warehouses->sortBy(function ($warehouse) use ($destLat, $destLng) {
                return $this->haversineDistance($warehouse->latitude, $warehouse->longitude, $destLat, $destLng);
            })->values();
        } else {
            // Fallback to priority if no coordinates
            $warehouses = $warehouses->sortBy('priority')->values();
        }

        return WarehouseAllocator::allocateByOrder($warehouses, $skuQuantities);
    }

    /**
     * Calculate distance between two points using Haversine formula.
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat        = deg2rad($lat2 - $lat1);
        $dLng        = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
