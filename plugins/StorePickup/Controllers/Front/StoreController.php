<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\StorePickup\Controllers\Front;

use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\StorePickup\Models\PickupStore;

class StoreController extends BaseController
{
    public function index(): mixed
    {
        $stores = PickupStore::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        return json_success('ok', $stores);
    }
}
