<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Plugin\Models;

use NiceShoply\Common\Models\BaseModel;

class Setting extends BaseModel
{
    protected $fillable = [
        'space', 'name', 'value', 'json',
    ];
}
