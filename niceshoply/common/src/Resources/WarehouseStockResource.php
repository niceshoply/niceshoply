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

class WarehouseStockResource extends JsonResource
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
            'id'                  => $this->id,
            'warehouse_id'        => $this->warehouse_id,
            'warehouse_name'      => $this->warehouse->name ?? '',
            'product_id'          => $this->product_id,
            'sku_id'              => $this->sku_id,
            'sku_code'            => $this->sku_code,
            'quantity'            => $this->quantity,
            'reserved_quantity'   => $this->reserved_quantity,
            'available_quantity'  => $this->available_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'updated_at'          => $this->updated_at,
        ];
    }
}
