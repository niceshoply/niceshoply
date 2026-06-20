<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Recharge\Models;

use Illuminate\Database\Eloquent\Model;

class RechargeOrder extends Model
{
    protected $table = 'recharge_orders';

    protected $guarded = [];

    protected $casts = [
        'amount'      => 'float',
        'bonus'       => 'float',
        'credited_at' => 'datetime',
    ];
}
