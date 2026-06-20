<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GroupBuy\Services;

use NiceShoply\Common\Services\Fee\BaseService;
use Throwable;

class GroupBuyFee extends BaseService
{
    public function addFee(): void
    {
        if (! (bool) plugin_setting('group_buy', 'enabled', true)) {
            return;
        }

        try {
            $checkoutData = $this->checkoutService->getCheckoutData();
        } catch (Throwable) {
            return;
        }

        $reference  = $checkoutData['reference'] ?? [];
        $activityId = (int) ($reference['group_buy_activity_id'] ?? 0);
        if ($activityId <= 0) {
            return;
        }

        $discount = GroupBuyService::getInstance()->computeDiscount($this->checkoutService->getCartList(), $activityId);
        if ($discount <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'group_buy',
            'title'        => __('GroupBuy::common.discount_title'),
            'total'        => round(-$discount, 2),
            'total_format' => '-'.currency_format($discount),
        ]);
    }
}
