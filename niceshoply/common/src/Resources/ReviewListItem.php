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

class ReviewListItem extends JsonResource
{
    /**
     * @throws Exception
     */
    public function toArray(Request $request): array
    {
        $customer = $this->customer;

        $data = [
            'id'                => $this->id,
            'customer_id'       => $customer->id ?? 0,
            'product_id'        => $this->product_id,
            'order_item_id'     => $this->order_item_id,
            'customer_name'     => $customer->name ?? 'Unknown',
            'customer_avatar'   => image_resize($customer->avatar ?? ''),
            'rating'            => $this->rating,
            'rating_dimensions' => $this->rating_dimensions ?? [],
            'title'             => $this->title ?? '',
            'content'           => $this->content,
            'images'            => $this->images ?? [],
            'has_images'        => $this->hasImages(),
            'reply'             => $this->reply,
            'reply_at'          => $this->reply_at,
            'status'            => $this->status,
            'like'              => $this->like,
            'dislike'           => $this->dislike,
            'active'            => $this->active,
            'created_at'        => $this->created_at,
        ];

        return fire_hook_filter('resource.review.item', $data);
    }
}
