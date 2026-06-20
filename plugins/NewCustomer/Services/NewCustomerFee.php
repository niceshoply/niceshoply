<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\NewCustomer\Services;

use NiceShoply\Common\Services\Fee\BaseService;
use Throwable;

/**
 * 新人首单立减费用项：仅对无历史订单的会员生效，折扣以负数写入费用列表。
 */
class NewCustomerFee extends BaseService
{
    public function addFee(): void
    {
        if (! (bool) plugin_setting('new_customer', 'enabled', true)) {
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

        $service = NewCustomerService::getInstance();
        if (! $service->isNewCustomer($customerId)) {
            return;
        }

        $subtotal = (float) collect($this->checkoutService->getCartList())->sum('subtotal');
        $discount = $service->computeDiscount($subtotal);
        if ($discount <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'new_customer',
            'title'        => __('NewCustomer::common.discount_title'),
            'total'        => round(-$discount, 2),
            'total_format' => '-'.currency_format($discount),
        ]);
    }
}
