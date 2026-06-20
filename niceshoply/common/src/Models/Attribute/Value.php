<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Attribute;

use Illuminate\Database\Eloquent\Relations\HasMany;
use NiceShoply\Common\Models\BaseModel;

class Value extends BaseModel
{
    protected $table = 'attribute_values';

    public $fillable = [
        'attribute_id',
    ];

    /**
     * Define translations relationship
     *
     * @return HasMany
     */
    public function translations(): HasMany
    {
        $class = \NiceShoply\Common\Models\Attribute\Value\Translation::class;

        return $this->hasMany($class, 'attribute_value_id', 'id');
    }

    /**
     * Locale translation object
     *
     * @return mixed
     * @throws \Exception
     */
    public function translation(): mixed
    {
        $class = \NiceShoply\Common\Models\Attribute\Value\Translation::class;

        return $this->hasOne($class, 'attribute_value_id', 'id')
            ->where('locale', locale_code());
    }
}
