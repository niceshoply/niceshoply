<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Membership\Services;

use NiceShoply\Common\Services\Fee\BaseService;
use Throwable;

class MembershipFee extends BaseService
{
    public function addFee(): void
    {
        if (! (bool) plugin_setting('membership', 'enabled', true)) {
            return;
        }

        try {
            $checkoutData = $this->checkoutService->getCheckoutData();
        } catch (Throwable) {
            return;
        }

        $customerId = (int) ($checkoutData['customer_id'] ?? 0);
        if ($customerId <= 0) {
            return;
        }

        $percent = MembershipService::getInstance()->discountPercent($customerId);
        if ($percent <= 0) {
            return;
        }

        $subtotal = (float) collect($this->checkoutService->getCartList())->sum('subtotal');
        $discount = round($subtotal * $percent / 100, 2);
        if ($discount <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'membership',
            'title'        => __('Membership::common.discount_title'),
            'total'        => round(-$discount, 2),
            'total_format' => '-'.currency_format($discount),
        ]);
    }
}
