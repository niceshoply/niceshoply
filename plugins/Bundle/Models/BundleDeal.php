<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bundle\Models;

use Illuminate\Database\Eloquent\Model;

class BundleDeal extends Model
{
    protected $table = 'bundle_deals';

    protected $guarded = [];

    protected $casts = [
        'items'        => 'array',
        'bundle_price' => 'decimal:2',
        'is_active'    => 'boolean',
    ];
}
