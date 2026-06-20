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
use Illuminate\Database\Eloquent\Relations\HasMany;
use NiceShoply\Common\Models\Attribute\Group;
use NiceShoply\Common\Models\Attribute\Value;
use NiceShoply\Common\Traits\Translatable;

class Attribute extends BaseModel
{
    use Translatable;

    protected $table = 'attributes';

    protected $fillable = [
        'category_id', 'attribute_group_id', 'position',
    ];

    /**
     * @return HasMany
     */
    public function values(): HasMany
    {
        return $this->hasMany(Value::class);
    }

    /**
     * @return HasMany
     */
    public function productAttributes(): HasMany
    {
        return $this->hasMany(Product\Attribute::class, 'attribute_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'attribute_group_id', 'id');
    }
}
