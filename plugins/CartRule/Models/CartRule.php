<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CartRule\Models;

use Illuminate\Database\Eloquent\Model;

class CartRule extends Model
{
    protected $table = 'cart_rules';

    protected $guarded = [];

    protected $casts = [
        'min_amount'     => 'float',
        'discount_value' => 'float',
        'max_discount'   => 'float',
        'active'         => 'boolean',
        'start_at'       => 'datetime',
        'end_at'         => 'datetime',
    ];
}
