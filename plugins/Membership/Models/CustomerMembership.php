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

class CustomerMembership extends Model
{
    protected $table = 'customer_memberships';

    protected $guarded = [];

    protected $casts = [
        'total_spent' => 'float',
    ];

    public function level()
    {
        return $this->belongsTo(MembershipLevel::class, 'level_id');
    }
}
