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

class GroupBuyGroup extends Model
{
    protected $table = 'group_buy_groups';

    protected $guarded = [];

    protected $casts = [
        'expire_at' => 'datetime',
    ];

    public function activity()
    {
        return $this->belongsTo(GroupBuyActivity::class, 'activity_id');
    }

    public function members()
    {
        return $this->hasMany(GroupBuyMember::class, 'group_id');
    }
}
