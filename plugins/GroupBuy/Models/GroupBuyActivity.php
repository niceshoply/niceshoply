<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GroupBuy\Models;

use Illuminate\Database\Eloquent\Model;

class GroupBuyActivity extends Model
{
    protected $table = 'group_buy_activities';

    protected $guarded = [];

    protected $casts = [
        'group_price' => 'float',
        'start_at'    => 'datetime',
        'end_at'      => 'datetime',
        'active'      => 'boolean',
    ];
}
