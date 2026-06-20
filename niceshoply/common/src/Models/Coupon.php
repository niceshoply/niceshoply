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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 优惠券模型
 */
class Coupon extends BaseModel
{
    use LogsActivity;

    protected $table = 'nice_coupons';

    protected $fillable = [
        'code',
        'promotion_id',
        'type',
        'value',
        'min_amount',
        'total_limit',
        'used_count',
        'per_customer_limit',
        'starts_at',
        'ends_at',
        'active',
    ];

    protected $casts = [
        'value'              => 'decimal:4',
        'min_amount'         => 'decimal:4',
        'total_limit'        => 'integer',
        'used_count'         => 'integer',
        'per_customer_limit' => 'integer',
        'active'             => 'boolean',
        'starts_at'          => 'datetime',
        'ends_at'            => 'datetime',
    ];

    /**
     * ActivityLog 审计配置。
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'type', 'value', 'active', 'used_count'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Coupon {$eventName}")
            ->useLogName('admin');
    }

    /**
     * 关联的促销活动（可空）。
     *
     * @return BelongsTo
     */
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class, 'promotion_id');
    }

    /**
     * 核销记录。
     *
     * @return HasMany
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class, 'coupon_id');
    }
}
