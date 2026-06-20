<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Fee;

use Exception;
use NiceShoply\Common\Services\Checkout\ShippingService;
use NiceShoply\Common\Services\Member\MemberLevelService;
use Throwable;

class Shipping extends BaseService
{
    /**
     * Get shipping fee.
     *
     * @return void
     * @throws Throwable
     */
    public function addFee(): void
    {
        $total = round($this->getShippingFee(), 2);

        $shippingFee = [
            'code'         => 'shipping',
            'title'        => 'Shipping',
            'total'        => $total,
            'total_format' => currency_format($total),
        ];

        $this->checkoutService->addFeeList($shippingFee);
    }

    /**
     * Calculate the shipping cost based on the current delivery method from the corresponding plugin.
     *
     * @return float
     * @throws Throwable
     */
    public function getShippingFee(): float
    {
        // 促销/优惠券命中免运费时，运费直接抵 0
        if ($this->checkoutService->isFreeShipping()) {
            return 0;
        }

        // 会员等级免运费
        if (MemberLevelService::getInstance()->customerHasFreeShipping($this->checkoutService->getCustomerId())) {
            return 0;
        }

        $checkoutData       = $this->checkoutService->getCheckoutData();
        $shippingMethodCode = $checkoutData['shipping_method_code'];
        $shippingMethods    = ShippingService::getInstance()->setCheckoutService($this->checkoutService)->getMethods();

        foreach ($shippingMethods as $shippingMethod) {
            foreach ($shippingMethod['quotes'] as $quote) {
                if ($quote['code'] == $shippingMethodCode) {
                    return (float) ($quote['cost'] ?? 0);
                }
            }
        }

        return 0;
    }

    /**
     * @param  $quoteCode
     * @return string
     * @throws Exception|Throwable
     */
    public function getShippingQuoteName($quoteCode): string
    {
        $shippingMethods = ShippingService::getInstance()->setCheckoutService($this->checkoutService)->getMethods();
        foreach ($shippingMethods as $shippingMethod) {
            foreach ($shippingMethod['quotes'] as $quote) {
                if ($quote['code'] == $quoteCode) {
                    return $quote['name'];
                }
            }
        }

        return '';
    }
}
