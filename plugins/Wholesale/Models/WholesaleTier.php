<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Wholesale\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleTier extends Model
{
    protected $table = 'wholesale_tiers';

    protected $guarded = [];

    protected $casts = [
        'min_qty'   => 'integer',
        'price'     => 'float',
        'is_active' => 'boolean',
    ];
}
