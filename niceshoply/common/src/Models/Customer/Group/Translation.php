<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Customer\Group;

use NiceShoply\Common\Models\BaseModel;

class Translation extends BaseModel
{
    protected $table = 'customer_group_translations';

    protected $fillable = [
        'locale', 'name', 'description',
    ];
}
