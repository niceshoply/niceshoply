<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 客户推送设备 Token（App FCM/APNs）
 */
class CustomerDeviceToken extends BaseModel
{
    protected $table = 'customer_device_tokens';

    protected $fillable = [
        'customer_id',
        'token',
        'platform',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
