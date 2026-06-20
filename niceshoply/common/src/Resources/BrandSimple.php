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

class BrandSimple extends JsonResource
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
            'id'            => $this->id,
            'first'         => $this->first,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'url'           => $this->url,
            'logo_url'      => image_resize($this->logo),
            'logo_original' => $this->logo,
            'logo_large'    => image_resize($this->logo, 300, 150),
            'logo_medium'   => image_resize($this->logo, 200, 100),
            'logo_small'    => image_resize($this->logo, 120, 60),
            'active'        => (bool) $this->active,
        ];

        return fire_hook_filter('resource.brand.simple', $data);
    }
}
