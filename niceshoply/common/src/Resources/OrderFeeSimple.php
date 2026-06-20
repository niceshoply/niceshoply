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

class OrderFeeSimple extends JsonResource
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
            'id'           => $this->id,
            'order_id'     => $this->order_id,
            'code'         => $this->code,
            'value'        => $this->value,
            'title'        => $this->title,
            'reference'    => $this->reference,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'value_format' => $this->value_format,
        ];

        return fire_hook_filter('resource.order_fee.simple', $data);
    }
}
