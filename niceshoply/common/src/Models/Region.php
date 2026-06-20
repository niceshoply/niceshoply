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

class Region extends BaseModel
{
    protected $table = 'regions';

    protected $fillable = [
        'name', 'description', 'position', 'active',
    ];

    /**
     * @return HasMany
     */
    public function regionStates(): HasMany
    {
        return $this->hasMany(\NiceShoply\Common\Models\Region\State::class);
    }
}
