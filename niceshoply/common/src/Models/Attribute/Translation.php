<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Attribute;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NiceShoply\Common\Models\Attribute;
use NiceShoply\Common\Models\BaseModel;

class Translation extends BaseModel
{
    protected $table = 'attribute_translations';

    protected $fillable = [
        'locale', 'name',
    ];

    /**
     * @return BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
