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

class ProductDetail extends JsonResource
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
        $sku = $this->masterSku;
        if (empty($sku)) {
            throw new Exception('Empty SKU for '.$this->id);
        }

        $images = [];
        foreach ($this->images as $image) {
            $resizedImage = image_resize($image, 600, 600);
            if ($resizedImage) {
                $images[] = $resizedImage;
            }
        }

        $skuImagePath = $sku->image;
        if ($skuImagePath) {
            $imageUrl = image_resize($skuImagePath, 600, 600);
            if ($imageUrl && ! in_array($imageUrl, $images)) {
                $images[] = $imageUrl;
            }
        }

        $data = [
            'sku_id'              => $sku->id,
            'product_id'          => $this->id,
            'slug'                => $this->slug,
            'url'                 => $this->url,
            'name'                => $this->translation->name,
            'summary'             => $this->translation->summary,
            'content'             => $this->translation->content,
            'image_small'         => $sku->getImageUrl(),
            'images'              => $images,
            'price_format'        => $sku->price_format,
            'origin_price_format' => $sku->origin_price_format,
            'sku'                 => (new SkuListItem($sku))->jsonSerialize(),
            'skus'                => SkuListItem::collection($this->skus)->jsonSerialize(),
            'variants'            => $this->variables,
            'attributes'          => $this->groupedAttributes(),
            'sales'               => $this->sales,
            'viewed'              => $this->viewed,
            'minimum'             => (int) ($this->minimum ?? 1),
            'related'             => ProductSimple::collection($this->relationProducts),
        ];

        return fire_hook_filter('resource.product.detail', $data);
    }
}
