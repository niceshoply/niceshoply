<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution\Models;

use Illuminate\Database\Eloquent\Model;

class DistributionCommission extends Model
{
    protected $table = 'distribution_commissions';

    protected $guarded = [];

    protected $casts = [
        'base_amount' => 'float',
        'rate'        => 'float',
        'amount'      => 'float',
    ];
}
