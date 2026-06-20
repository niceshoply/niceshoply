<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 客户积分账户
 */
class CustomerPoint extends BaseModel
{
    protected $table = 'nice_customer_points';

    protected $fillable = ['customer_id', 'balance', 'total_earned', 'total_spent'];

    protected $casts = [
        'customer_id'  => 'integer',
        'balance'      => 'integer',
        'total_earned' => 'integer',
        'total_spent'  => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
