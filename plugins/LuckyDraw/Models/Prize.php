<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\LuckyDraw\Models;

use Illuminate\Database\Eloquent\Model;

class Prize extends Model
{
    protected $table = 'lucky_prizes';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
