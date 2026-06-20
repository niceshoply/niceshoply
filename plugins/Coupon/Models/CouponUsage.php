<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    protected $table = 'coupon_usages';

    protected $guarded = [];

    protected $casts = [
        'discount' => 'float',
    ];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }
}
