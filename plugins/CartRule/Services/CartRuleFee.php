<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CartRule\Services;

use NiceShoply\Common\Services\Fee\BaseService;

class CartRuleFee extends BaseService
{
    public function addFee(): void
    {
        if (! (bool) plugin_setting('cart_rule', 'enabled', true)) {
            return;
        }

        $subtotal = (float) collect($this->checkoutService->getCartList())->sum('subtotal');
        if ($subtotal <= 0) {
            return;
        }

        $result   = CartRuleService::getInstance()->bestDiscount($subtotal);
        $discount = $result['discount'];
        if ($discount <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'cart_rule',
            'title'        => __('CartRule::common.discount_title'),
            'total'        => round(-$discount, 2),
            'total_format' => '-'.currency_format($discount),
        ]);
    }
}
