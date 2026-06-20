<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 客户登录日志。
 */
class CustomerLoginLog extends BaseModel
{
    protected $table = 'nice_customer_login_logs';

    protected $fillable = [
        'customer_id', 'ip', 'user_agent', 'success', 'failure_reason', 'is_new_device',
    ];

    protected $casts = [
        'success'       => 'boolean',
        'is_new_device' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
