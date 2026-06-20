<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FreightInsurance\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceRecord extends Model
{
    protected $table = 'freight_insurance_records';

    protected $guarded = [];

    protected $casts = [
        'premium' => 'float',
    ];
}
