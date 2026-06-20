<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Fee;

use NiceShoply\Common\Repositories\TaxRateRepo;
use Throwable;

class Tax extends BaseService
{
    /**
     * @return void
     * @throws Throwable
     */
    public function addFee(): void
    {
        $taxes = $this->getTaxes();

        // 税基-折扣口径：当 tax_base_include_discount 为真时，税基扣除折扣，
        // 按「(小计 − 已应用折扣) / 小计」比例缩减各税率税额（比例分摊）。
        $ratio = $this->getDiscountRatio();

        foreach ($taxes as $taxRateId => $value) {
            $value = $value * $ratio;
            if ($value <= 0) {
                continue;
            }
            $taxFee = [
                'code'         => 'tax',
                'title'        => TaxRateRepo::getInstance()->getNameByRateId($taxRateId),
                'total'        => round($value, 2),
                'total_format' => currency_format($value),
            ];
            $this->checkoutService->addFeeList($taxFee);
        }
    }

    /**
     * 计算税基折扣比例（默认 1，即税基不含折扣）。
     *
     * @return float
     */
    private function getDiscountRatio(): float
    {
        if (! system_setting('tax_base_include_discount', false)) {
            return 1.0;
        }

        $subtotal = $this->checkoutService->getSubTotal();
        if ($subtotal <= 0) {
            return 1.0;
        }

        $remaining = $this->checkoutService->getDiscountedSubtotalRemaining();

        return max(0.0, min(1.0, $remaining / $subtotal));
    }

    /**
     * Get all taxes by address and product.
     *
     * @return array
     * @throws Throwable
     */
    public function getTaxes(): array
    {
        $taxes = [];

        $shippingAddress = $this->checkoutService->getCheckout()->shippingAddress;
        $billingAddress  = $this->checkoutService->getCheckout()->billingAddress;
        $addressInfo     = [
            'shipping_address' => $shippingAddress,
            'billing_address'  => $billingAddress,
        ];

        $taxLib = \NiceShoply\Common\Libraries\Tax::getInstance($addressInfo);

        foreach ($this->checkoutService->getCartList() as $product) {
            if (empty($product['tax_class_id'])) {
                continue;
            }

            $taxRates = $taxLib->getRates($product['price'], $product['tax_class_id']);
            foreach ($taxRates as $taxRate) {
                if (! isset($taxes[$taxRate['tax_rate_id']])) {
                    $taxes[$taxRate['tax_rate_id']] = ($taxRate['amount'] * $product['quantity']);
                } else {
                    $taxes[$taxRate['tax_rate_id']] += ($taxRate['amount'] * $product['quantity']);
                }
            }
        }

        return $taxes;
    }
}
