<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Wholesale\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Wholesale\Services\WholesaleService;

class WholesaleController extends BaseController
{
    /**
     * 指定 SKU 的阶梯价表（商详页展示）。
     */
    public function tiers(Request $request): mixed
    {
        $skuId = (int) $request->query('sku_id', 0);

        return json_success('ok', WholesaleService::getInstance()->tiersForSku($skuId));
    }
}
