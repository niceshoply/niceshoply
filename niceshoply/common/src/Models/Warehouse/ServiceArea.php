<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models\Warehouse;

use NiceShoply\Common\Models\BaseModel;

class ServiceArea extends BaseModel
{
    protected $table = 'warehouse_service_areas';

    protected $fillable = ['warehouse_id', 'country_id', 'state_id'];
}
