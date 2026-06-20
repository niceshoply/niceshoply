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

class ArticleName extends JsonResource
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
            'id'         => $this->id,
            'slug'       => $this->slug,
            'name'       => $this->fallbackName('title'),
            'image'      => image_resize($this->image, 200, 150),
            'active'     => (bool) $this->active,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];

        return fire_hook_filter('resource.article.name', $data);
    }
}
