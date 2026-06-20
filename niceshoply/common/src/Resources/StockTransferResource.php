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

class StockTransferResource extends JsonResource
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
            'number'              => $this->number,
            'from_warehouse_id'   => $this->from_warehouse_id,
            'from_warehouse_name' => $this->fromWarehouse->name ?? '',
            'to_warehouse_id'     => $this->to_warehouse_id,
            'to_warehouse_name'   => $this->toWarehouse->name ?? '',
            'status'              => $this->status,
            'note'                => $this->note,
            'admin_id'            => $this->admin_id,
            'shipped_at'          => $this->shipped_at,
            'completed_at'        => $this->completed_at,
            'items'               => ShipmentItemResource::collection($this->whenLoaded('items')),
            'created_at'          => $this->created_at,
        ];
    }
}
