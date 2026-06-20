<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Attribute\Group;

use NiceShoply\Common\Models\BaseModel;

class Translation extends BaseModel
{
    protected $table = 'attribute_group_translations';

    protected $fillable = [
        'attribute_group_id', 'locale', 'name',
    ];
}
