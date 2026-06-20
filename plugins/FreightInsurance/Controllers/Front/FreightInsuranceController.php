<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FreightInsurance\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\FreightInsurance\Services\FreightInsuranceService;

class FreightInsuranceController extends BaseController
{
    /**
     * 运费险报价（前台结算页展示保费）。
     */
    public function quote(Request $request): mixed
    {
        $service = FreightInsuranceService::getInstance();
        if (! $service->enabled()) {
            return json_success('ok', ['enabled' => false, 'premium' => 0]);
        }

        $subtotal = (float) $request->input('subtotal', 0);
        $premium  = $service->computePremium($subtotal);

        return json_success('ok', [
            'enabled'        => true,
            'premium'        => $premium,
            'premium_format' => currency_format($premium),
        ]);
    }
}
