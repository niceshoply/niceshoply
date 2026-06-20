<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Fee;

use NiceShoply\Common\Services\Promotion\CouponService;

/**
 * 优惠券折扣费用项。
 *
 * 读取结账上下文中已应用的券码，校验通过后以「负费用项」追加进 feeList。
 * 折扣封顶为「小计 − 已应用折扣」，保证叠加促销后不会出现负数小计。
 */
class Coupon extends BaseService
{
    /**
     * @return void
     */
    public function addFee(): void
    {
        $code = $this->checkoutService->getAppliedCouponCode();
        if ($code === '') {
            return;
        }

        $result = CouponService::getInstance()->validate($code, $this->checkoutService);
        if (! $result['valid'] || empty($result['coupon'])) {
            // 校验失败时静默跳过（用户在「应用券码」时已得到错误提示）
            return;
        }

        $coupon = $result['coupon'];

        if (! empty($result['free_shipping'])) {
            $this->checkoutService->markFreeShipping();
        }

        // 封顶：不超过当前剩余可抵额（小计 − 已应用的促销折扣）
        $remaining = $this->checkoutService->getDiscountedSubtotalRemaining();
        $amount    = round(min((float) $result['discount'], $remaining), 2);

        if ($amount > 0) {
            $total = -$amount;

            $this->checkoutService->addFeeList([
                'code'         => 'coupon',
                'title'        => trans('front/coupon.coupon_title', ['code' => $coupon->code]),
                'total'        => $total,
                'total_format' => currency_format($total),
            ]);
        }

        $this->checkoutService->recordAppliedDiscount([
            'type'          => 'coupon',
            'promotion_id'  => $coupon->promotion_id,
            'coupon_id'     => $coupon->id,
            'code'          => $coupon->code,
            'label'         => trans('front/coupon.coupon_title', ['code' => $coupon->code]),
            'amount'        => $amount,
            'free_shipping' => (bool) $result['free_shipping'],
            'customer_id'   => $this->checkoutService->getCustomerId(),
            'snapshot'      => [
                'type'  => $coupon->type,
                'value' => (float) $coupon->value,
            ],
        ]);
    }
}
