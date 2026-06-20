<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Presale\Models;

use Illuminate\Database\Eloquent\Model;

class PresaleItem extends Model
{
    protected $table = 'presale_items';

    protected $guarded = [];

    protected $casts = [
        'presale_price' => 'float',
        'deposit'       => 'float',
        'expand'        => 'float',
    ];
}
