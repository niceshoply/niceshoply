<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FreightInsurance\Services;

use NiceShoply\Common\Services\Fee\BaseService;

class FreightInsuranceFee extends BaseService
{
    public function addFee(): void
    {
        $service = FreightInsuranceService::getInstance();
        if (! $service->enabled()) {
            return;
        }

        // 仅当用户选择投保（前端传 freight_insurance=1）时收取保费
        if (! request()->boolean('freight_insurance')) {
            return;
        }

        $subtotal = $service->cartSubtotal($this->checkoutService->getCartList());
        $premium  = $service->computePremium($subtotal);
        if ($premium <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'freight_insurance',
            'title'        => __('FreightInsurance::common.fee_title'),
            'total'        => $premium,
            'total_format' => currency_format($premium),
        ]);
    }
}
