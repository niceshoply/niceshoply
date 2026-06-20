<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CouponCenter\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\CouponCenter\Services\CouponCenterService;
use Throwable;

class CouponCenterController extends BaseController
{
    public function list(): mixed
    {
        $customerId = (int) (token_customer_id() ?? 0);

        return json_success('ok', CouponCenterService::getInstance()->claimable($customerId));
    }

    public function claim(Request $request): mixed
    {
        try {
            $couponId = (int) $request->input('coupon_id', 0);
            $customerId = (int) (token_customer_id() ?? 0);
            $result = CouponCenterService::getInstance()->claim($customerId, $couponId);

            return json_success(__('CouponCenter::common.claimed'), $result);
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }

    public function mine(): mixed
    {
        $customerId = (int) (token_customer_id() ?? 0);

        return json_success('ok', CouponCenterService::getInstance()->mine($customerId));
    }
}
