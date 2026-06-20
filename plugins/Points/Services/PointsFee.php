<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Points\Services;

use NiceShoply\Common\Services\Fee\BaseService;
use Throwable;

class PointsFee extends BaseService
{
    public function addFee(): void
    {
        if (! (bool) plugin_setting('points', 'enabled', true)) {
            return;
        }

        try {
            $checkoutData = $this->checkoutService->getCheckoutData();
        } catch (Throwable) {
            return;
        }

        $reference = $checkoutData['reference'] ?? [];
        $usePoints = (int) ($reference['use_points'] ?? 0);
        if ($usePoints <= 0) {
            return;
        }

        $customerId = (int) ($checkoutData['customer_id'] ?? 0);
        if ($customerId <= 0) {
            return;
        }

        $subtotal = (float) collect($this->checkoutService->getCartList())->sum('subtotal');
        $result   = PointService::getInstance()->computeRedeem($customerId, $usePoints, $subtotal);

        if ($result['discount'] <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'points',
            'title'        => __('Points::common.redeem_title'),
            'total'        => round(-$result['discount'], 2),
            'total_format' => '-'.currency_format($result['discount']),
        ]);
    }
}
