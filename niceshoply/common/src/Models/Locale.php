<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

class Locale extends BaseModel
{
    protected $fillable = [
        'name', 'code', 'image', 'position', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
