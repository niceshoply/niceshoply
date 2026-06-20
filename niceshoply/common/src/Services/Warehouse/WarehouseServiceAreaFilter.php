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
use NiceShoply\Common\Models\Warehouse;

class WarehouseServiceAreaFilter
{
    /**
     * Filter active warehouses by destination address service area.
     *
     * @param  array  $destAddress  ['country_id' => ..., 'state_id' => ...]
     * @return array{matched: Collection, fallback: Collection}
     */
    public static function filter(array $destAddress): array
    {
        $countryId = (int) ($destAddress['country_id'] ?? 0);
        $stateId   = (int) ($destAddress['state_id'] ?? 0);

        $warehouses = Warehouse::query()
            ->where('active', true)
            ->with('serviceAreas')
            ->get();

        $matched  = collect();
        $fallback = collect();

        foreach ($warehouses as $warehouse) {
            if ($warehouse->servesAddress($countryId, $stateId)) {
                $matched->push($warehouse);
            } else {
                $fallback->push($warehouse);
            }
        }

        return ['matched' => $matched, 'fallback' => $fallback];
    }
}
