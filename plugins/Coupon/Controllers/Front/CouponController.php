<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Coupon\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Coupon\Services\CouponService;

class CouponController extends BaseController
{
    /**
     * 应用优惠券：校验后写入 checkout.reference.coupon_code。
     */
    public function apply(Request $request): mixed
    {
        try {
            $code = trim((string) $request->get('code'));
            if ($code === '') {
                return json_fail(__('Coupon::common.code_required'));
            }

            $customerId = (int) token_customer_id();
            $checkout   = CheckoutService::getInstance($customerId);
            $subtotal   = (float) collect($checkout->getCartList())->sum('subtotal');

            $coupon = CouponService::getInstance()->validate($code, $subtotal, $customerId);

            $checkoutData          = $checkout->getCheckoutData();
            $reference             = $checkoutData['reference'] ?? [];
            $reference['coupon_code'] = $coupon->code;
            $checkout->updateValues(['reference' => $reference]);

            return json_success(__('Coupon::common.applied'), [
                'code' => $coupon->code,
                'name' => $coupon->name,
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 移除优惠券。
     */
    public function remove(): mixed
    {
        try {
            $checkout              = CheckoutService::getInstance((int) token_customer_id());
            $checkoutData          = $checkout->getCheckoutData();
            $reference             = $checkoutData['reference'] ?? [];
            unset($reference['coupon_code']);
            $checkout->updateValues(['reference' => $reference]);

            return json_success(__('Coupon::common.removed'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
