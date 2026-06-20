<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon\Services;

use NiceShoply\Common\Services\Fee\BaseService;
use NiceShoply\Common\Services\Fee\Shipping;
use Throwable;

/**
 * 优惠券折扣费用项。作为 fee 注入 service.checkout.fee.methods，
 * 折扣以负数形式写入费用列表，从而自动反映到订单总额。
 */
class CouponFee extends BaseService
{
    public function addFee(): void
    {
        if (! (bool) plugin_setting('coupon', 'enabled', true)) {
            return;
        }

        try {
            $checkoutData = $this->checkoutService->getCheckoutData();
        } catch (Throwable) {
            return;
        }

        $reference = $checkoutData['reference'] ?? [];
        $code      = $reference['coupon_code'] ?? null;
        if (! $code) {
            return;
        }

        $subtotal   = (float) collect($this->checkoutService->getCartList())->sum('subtotal');
        $customerId = (int) ($checkoutData['customer_id'] ?? 0);

        $coupon = CouponService::getInstance()->tryValidate($code, $subtotal, $customerId);
        if (! $coupon) {
            return;
        }

        $shippingFee = 0;
        if ($coupon->type === 'free_shipping') {
            try {
                $shippingFee = (new Shipping($this->checkoutService))->getShippingFee();
            } catch (Throwable) {
                $shippingFee = 0;
            }
        }

        $discount = CouponService::getInstance()->computeDiscount($coupon, $subtotal, $shippingFee);
        if ($discount <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'coupon',
            'title'        => __('Coupon::common.discount_title'),
            'total'        => round(-$discount, 2),
            'total_format' => '-'.currency_format($discount),
        ]);
    }
}
