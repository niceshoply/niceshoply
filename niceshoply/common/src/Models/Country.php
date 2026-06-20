<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use NiceShoply\Common\Repositories\CountryRepo;

class Country extends BaseModel
{
    protected $table = 'countries';

    protected $fillable = [
        'name', 'code', 'continent', 'position', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => CountryRepo::clearCache());
        static::deleted(fn () => CountryRepo::clearCache());
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class, 'country_id', 'id');
    }
}
