<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Wholesale\Services;

use NiceShoply\Common\Services\Fee\BaseService;

class WholesaleFee extends BaseService
{
    public function addFee(): void
    {
        if (! (bool) plugin_setting('wholesale', 'enabled', true)) {
            return;
        }

        $cartList = $this->checkoutService->getCartList();
        $discount = WholesaleService::getInstance()->computeDiscount($cartList);
        if ($discount <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'wholesale',
            'title'        => __('Wholesale::common.discount_title'),
            'total'        => round(-$discount, 2),
            'total_format' => '-'.currency_format($discount),
        ]);
    }
}
