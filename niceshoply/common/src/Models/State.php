<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class State extends BaseModel
{
    protected $table = 'states';

    protected $fillable = [
        'country_id', 'country_code', 'name', 'code', 'position', 'active',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
