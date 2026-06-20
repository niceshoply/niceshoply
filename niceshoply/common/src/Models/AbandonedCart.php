<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 弃购购物车记录。
 */
class AbandonedCart extends BaseModel
{
    protected $table = 'nice_abandoned_carts';

    protected $fillable = [
        'cart_key', 'customer_id', 'guest_id', 'email', 'cart_snapshot', 'cart_total',
        'currency_code', 'coupon_id', 'coupon_code', 'reminder_count', 'last_reminded_at',
        'converted', 'converted_order_id', 'converted_at',
    ];

    protected $casts = [
        'cart_snapshot'    => 'array',
        'cart_total'       => 'decimal:4',
        'reminder_count'   => 'integer',
        'converted'        => 'boolean',
        'last_reminded_at' => 'datetime',
        'converted_at'     => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }
}
