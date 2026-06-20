<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MultiWarehouse\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\MultiWarehouse\Services\WarehouseService;

class WarehouseController extends BaseController
{
    public function stock(int $skuId): mixed
    {
        return json_success('ok', WarehouseService::getInstance()->stockBySku($skuId));
    }

    public function allocate(Request $request, int $skuId): mixed
    {
        $qty = max(1, (int) $request->input('quantity', 1));

        return json_success('ok', WarehouseService::getInstance()->allocate(
            $skuId,
            $qty,
            $request->input('province'),
            $request->input('city')
        ) ?? []);
    }
}
