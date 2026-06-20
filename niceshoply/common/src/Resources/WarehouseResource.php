<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'code'          => $this->code,
            'name'          => $this->name,
            'description'   => $this->description,
            'contact_name'  => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'country_id'    => $this->country_id,
            'country'       => $this->country,
            'state_id'      => $this->state_id,
            'state'         => $this->state,
            'city'          => $this->city,
            'address_1'     => $this->address_1,
            'address_2'     => $this->address_2,
            'zipcode'       => $this->zipcode,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'priority'      => $this->priority,
            'is_default'    => (bool) $this->is_default,
            'active'        => (bool) $this->active,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
