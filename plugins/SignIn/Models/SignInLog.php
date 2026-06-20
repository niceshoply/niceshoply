<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SignIn\Models;

use Illuminate\Database\Eloquent\Model;

class SignInLog extends Model
{
    protected $table = 'sign_in_logs';

    protected $guarded = [];

    protected $casts = [
        'sign_date' => 'date',
    ];
}
