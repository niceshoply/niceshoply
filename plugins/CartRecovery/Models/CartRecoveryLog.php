<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CartRecovery\Models;

use Illuminate\Database\Eloquent\Model;

class CartRecoveryLog extends Model
{
    protected $table = 'cart_recovery_logs';

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
