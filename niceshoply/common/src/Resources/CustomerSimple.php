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

class CustomerSimple extends JsonResource
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
            'email'        => $this->email,
            'name'         => $this->name,
            'avatar'       => image_resize($this->avatar),
            'locale'       => $this->locale,
            'has_password' => $this->has_password,
        ];

        return fire_hook_filter('resource.customer.simple', $data);
    }
}
