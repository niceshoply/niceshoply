<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Resources;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderShipment extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @throws Exception
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id'              => $this->id,
            'warehouse_id'    => $this->warehouse_id,
            'warehouse_name'  => $this->warehouse_name,
            'express_code'    => $this->express_code,
            'express_company' => $this->express_company,
            'express_number'  => $this->express_number,
            'status'          => $this->status ?? '',
            'shipped_at'      => $this->shipped_at,
            'delivered_at'    => $this->delivered_at,
            'items'           => ShipmentItemResource::collection($this->whenLoaded('items')),
            'created_at'      => $this->created_at,
        ];

        return fire_hook_filter('resource.order.shipment', $data);
    }
}
