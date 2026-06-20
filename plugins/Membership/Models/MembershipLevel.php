<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Membership\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipLevel extends Model
{
    protected $table = 'membership_levels';

    protected $guarded = [];

    protected $casts = [
        'min_spent'        => 'float',
        'discount_percent' => 'float',
        'active'           => 'boolean',
    ];
}
