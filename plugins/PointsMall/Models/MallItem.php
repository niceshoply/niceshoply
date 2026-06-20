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

class MallItem extends Model
{
    protected $table = 'points_mall_items';

    protected $guarded = [];

    protected $casts = [
        'is_active'  => 'boolean',
        'cash_cost'  => 'float',
    ];
}
