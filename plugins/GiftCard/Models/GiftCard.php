<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GiftCard\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    protected $table = 'gift_cards';

    protected $guarded = [];

    protected $hidden = ['pin'];

    protected $casts = [
        'face_value'  => 'float',
        'balance'     => 'float',
        'redeemed_at' => 'datetime',
        'expire_at'   => 'date',
    ];
}
