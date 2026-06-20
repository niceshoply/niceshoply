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

class TaxClass extends BaseModel
{
    protected $table = 'tax_classes';

    protected $fillable = [
        'name', 'description',
    ];

    /**
     * @return HasMany
     */
    public function taxRules(): HasMany
    {
        return $this->hasMany(TaxRule::class);
    }
}
