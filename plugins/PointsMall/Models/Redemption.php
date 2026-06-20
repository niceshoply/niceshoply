<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PointsMall\Models;

use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    protected $table = 'points_mall_redemptions';

    protected $guarded = [];

    protected $casts = [
        'cash_cost' => 'float',
    ];
}
