<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\NotifyCenter\Models;

use Illuminate\Database\Eloquent\Model;

class MemberNotification extends Model
{
    protected $table = 'member_notifications';

    protected $guarded = [];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
