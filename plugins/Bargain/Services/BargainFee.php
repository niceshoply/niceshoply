<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bargain\Services;

use NiceShoply\Common\Services\Fee\BaseService;
use Throwable;

class BargainFee extends BaseService
{
    public function addFee(): void
    {
        if (! (bool) plugin_setting('bargain', 'enabled', true)) {
            return;
        }

        try {
            $checkoutData = $this->checkoutService->getCheckoutData();
        } catch (Throwable) {
            return;
        }

        $reference = $checkoutData['reference'] ?? [];
        $taskId    = (int) ($reference['bargain_task_id'] ?? 0);
        if ($taskId <= 0) {
            return;
        }

        $customerId = (int) ($checkoutData['customer_id'] ?? 0);
        $discount   = BargainService::getInstance()->computeDiscount($this->checkoutService->getCartList(), $taskId, $customerId);
        if ($discount <= 0) {
            return;
        }

        $this->checkoutService->addFeeList([
            'code'         => 'bargain',
            'title'        => __('Bargain::common.discount_title'),
            'total'        => round(-$discount, 2),
            'total_format' => '-'.currency_format($discount),
        ]);
    }
}
