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

class TaxRate extends BaseModel
{
    protected $table = 'tax_rates';

    protected $fillable = [
        'region_id', 'name', 'type', 'rate', 'scheme', 'requires_tax_id',
    ];

    protected $casts = [
        'requires_tax_id' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
