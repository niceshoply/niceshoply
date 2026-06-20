<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailMarketing\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSubscriber extends Model
{
    protected $table = 'email_subscribers';

    protected $guarded = [];

    protected $casts = [
        'subscribed' => 'boolean',
    ];
}
