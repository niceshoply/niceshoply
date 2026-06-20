<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use NiceShoply\Common\Models\Order\Shipment;

class Warehouse extends BaseModel
{
    use SoftDeletes;

    protected $table = 'warehouses';

    protected $fillable = [
        'code', 'name', 'description', 'contact_name', 'contact_phone',
        'country_id', 'country', 'state_id', 'state', 'city',
        'address_1', 'address_2', 'zipcode', 'latitude', 'longitude',
        'priority', 'is_default', 'active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'active'     => 'boolean',
        'latitude'   => 'float',
        'longitude'  => 'float',
    ];

    /**
     * @return HasMany
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Warehouse\Stock::class, 'warehouse_id');
    }

    /**
     * @return HasMany
     */
    public function movements(): HasMany
    {
        return $this->hasMany(Warehouse\StockMovement::class, 'warehouse_id');
    }

    /**
     * @return HasMany
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'warehouse_id');
    }

    /**
     * @return HasMany
     */
    public function serviceAreas(): HasMany
    {
        return $this->hasMany(Warehouse\ServiceArea::class, 'warehouse_id');
    }

    /**
     * Check if this warehouse serves a given address.
     * Warehouses with no service areas are considered global.
     *
     * @param  int  $countryId
     * @param  int  $stateId
     * @return bool
     */
    public function servesAddress(int $countryId, int $stateId = 0): bool
    {
        if ($this->serviceAreas->isEmpty()) {
            return true;
        }

        return $this->serviceAreas->contains(function ($area) use ($countryId, $stateId) {
            if ($area->country_id != $countryId) {
                return false;
            }

            return $area->state_id == 0 || $area->state_id == $stateId;
        });
    }

    /**
     * Convert warehouse address to array for shipping calculation.
     *
     * @return array
     */
    public function toAddressArray(): array
    {
        return [
            'country_id' => $this->country_id,
            'country'    => $this->country,
            'state_id'   => $this->state_id,
            'state'      => $this->state,
            'city'       => $this->city,
            'address_1'  => $this->address_1,
            'address_2'  => $this->address_2,
            'zipcode'    => $this->zipcode,
            'latitude'   => $this->latitude,
            'longitude'  => $this->longitude,
        ];
    }
}
