<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use NiceShoply\Common\Repositories\CurrencyRepo;

class Currency extends BaseModel
{
    protected $table = 'currencies';

    protected $fillable = [
        'name', 'code', 'symbol_left', 'symbol_right', 'decimal_place', 'value', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => CurrencyRepo::clearCache());
        static::deleted(fn () => CurrencyRepo::clearCache());
    }
}
