<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 积分流水
 */
class PointLog extends BaseModel
{
    protected $table = 'nice_point_logs';

    public const TYPE_EARN = 'earn';

    public const TYPE_SPEND = 'spend';

    public const TYPE_EXPIRE = 'expire';

    public const TYPE_ADJUST = 'adjust';

    protected $fillable = [
        'customer_id', 'type', 'points', 'source', 'reference_id', 'expires_at', 'comment',
    ];

    protected $casts = [
        'customer_id'  => 'integer',
        'points'       => 'integer',
        'reference_id' => 'integer',
        'expires_at'   => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
