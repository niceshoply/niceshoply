<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\OfflineRedeem\Models;

use Illuminate\Database\Eloquent\Model;

class RedeemCode extends Model
{
    protected $table = 'redeem_codes';

    protected $guarded = [];

    protected $casts = [
        'expires_at'  => 'datetime',
        'redeemed_at' => 'datetime',
    ];
}
