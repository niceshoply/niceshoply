<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FlashSale\Services;

use NiceShoply\Common\Services\Fee\BaseService;

class FlashSaleFee extends BaseService
{
    public function addFee(): void
    {
        if (! (bool) plugin_setting('flash_sale', 'enabled', true)) {
            return;
        }

        $cartList = $this->checkoutService->getCartList();
        $discount = FlashSaleService::getInstance()->computeDiscount($cartList);
        if ($discount <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'flash_sale',
            'title'        => __('FlashSale::common.discount_title'),
            'total'        => round(-$discount, 2),
            'total_format' => '-'.currency_format($discount),
        ]);
    }
}
