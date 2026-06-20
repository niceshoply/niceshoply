<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\Order\Item;

class Review extends BaseModel
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $table = 'reviews';

    protected $fillable = [
        'customer_id', 'product_id', 'order_item_id', 'rating', 'rating_dimensions',
        'content', 'images', 'reply', 'reply_at', 'like', 'dislike', 'active', 'status',
    ];

    protected $casts = [
        'active'            => 'boolean',
        'images'            => 'array',
        'rating_dimensions' => 'array',
        'reply_at'          => 'datetime',
    ];

    /**
     * 是否含晒图。
     */
    public function hasImages(): bool
    {
        return ! empty($this->images);
    }

    /**
     * 前台是否可见。
     */
    public function isPublic(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->active;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'order_item_id', 'id');
    }
}
