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

class BargainRecord extends Model
{
    protected $table = 'bargain_records';

    protected $guarded = [];

    protected $casts = [
        'cut_amount' => 'float',
    ];
}
