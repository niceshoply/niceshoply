<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Ewaybill\Models;

use Illuminate\Database\Eloquent\Model;

class EWaybill extends Model
{
    protected $table = 'ewaybills';

    protected $guarded = [];

    protected $casts = [
        'raw'        => 'array',
        'print_data' => 'array',
    ];
}
