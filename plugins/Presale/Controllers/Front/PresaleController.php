<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Presale\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Presale\Services\PresaleService;

class PresaleController extends BaseController
{
    public function info(Request $request): mixed
    {
        $skuId = (int) $request->get('sku_id');
        $info  = PresaleService::getInstance()->infoForSku($skuId);

        return json_success('ok', $info);
    }
}
