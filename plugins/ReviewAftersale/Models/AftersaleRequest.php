<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReviewAftersale\Models;

use Illuminate\Database\Eloquent\Model;

class AftersaleRequest extends Model
{
    protected $table = 'aftersale_requests';

    protected $guarded = [];

    protected $casts = [
        'images'        => 'array',
        'refund_amount' => 'float',
    ];
}
