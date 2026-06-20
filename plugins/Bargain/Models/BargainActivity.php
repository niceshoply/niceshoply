<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bargain\Models;

use Illuminate\Database\Eloquent\Model;

class BargainActivity extends Model
{
    protected $table = 'bargain_activities';

    protected $guarded = [];

    protected $casts = [
        'origin_price' => 'float',
        'floor_price'  => 'float',
        'min_cut'      => 'float',
        'max_cut'      => 'float',
        'start_at'     => 'datetime',
        'end_at'       => 'datetime',
        'active'       => 'boolean',
    ];
}
