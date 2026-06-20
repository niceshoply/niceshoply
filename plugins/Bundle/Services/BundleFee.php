<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bundle\Services;

use NiceShoply\Common\Services\Fee\BaseService;

class BundleFee extends BaseService
{
    public function addFee(): void
    {
        $service = BundleService::getInstance();
        if (! $service->enabled()) {
            return;
        }

        $result = $service->matchDiscount($this->checkoutService->getCartList());
        if ($result['discount'] <= 0) {
            return;
        }

        $total = -1 * $result['discount'];
        $title = __('Bundle::common.fee_title');
        if (! empty($result['titles'])) {
            $title .= '（'.implode('、', $result['titles']).'）';
        }

        $this->checkoutService->addFeeList([
            'code'         => 'bundle_discount',
            'title'        => $title,
            'total'        => $total,
            'total_format' => currency_format($total),
        ]);
    }
}
