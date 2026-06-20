<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MultiCurrency\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRegionDefault extends Model
{
    protected $table = 'currency_region_defaults';

    public $timestamps = false;

    protected $guarded = [];
}
