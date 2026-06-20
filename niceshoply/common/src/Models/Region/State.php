<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Region;

use NiceShoply\Common\Models\BaseModel;

class State extends BaseModel
{
    protected $table = 'region_states';

    protected $fillable = [
        'country_id', 'state_id',
    ];
}
