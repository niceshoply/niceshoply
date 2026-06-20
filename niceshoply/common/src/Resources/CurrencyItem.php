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

class CurrencyItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id'            => $this->id,
            'name'          => $this->name,
            'code'          => $this->code,
            'symbol_left'   => $this->symbol_left,
            'symbol_right'  => $this->symbol_right,
            'decimal_place' => $this->decimal_place,
            'value'         => $this->value,
            'active'        => $this->active,
        ];

        return fire_hook_filter('resource.currency.item', $data);
    }
}
