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

/**
 * 优惠券核销记录模型
 */
class CouponUsage extends BaseModel
{
    protected $table = 'nice_coupon_usages';

    protected $fillable = [
        'coupon_id',
        'customer_id',
        'order_id',
        'discount_amount',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:4',
        'used_at'         => 'datetime',
    ];

    /**
     * 关联优惠券。
     *
     * @return BelongsTo
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
}
