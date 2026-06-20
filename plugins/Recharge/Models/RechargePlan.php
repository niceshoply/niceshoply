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

class RechargePlan extends Model
{
    protected $table = 'recharge_plans';

    protected $guarded = [];

    protected $casts = [
        'amount'    => 'float',
        'bonus'     => 'float',
        'is_active' => 'boolean',
    ];
}
