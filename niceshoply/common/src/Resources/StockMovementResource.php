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

class StockMovementResource extends JsonResource
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
            'id'             => $this->id,
            'warehouse_id'   => $this->warehouse_id,
            'warehouse_name' => $this->warehouse->name ?? '',
            'sku_code'       => $this->sku_code,
            'quantity'       => $this->quantity,
            'type'           => $this->type,
            'reference_type' => $this->reference_type,
            'reference_id'   => $this->reference_id,
            'note'           => $this->note,
            'admin_id'       => $this->admin_id,
            'created_at'     => $this->created_at,
        ];
    }
}
