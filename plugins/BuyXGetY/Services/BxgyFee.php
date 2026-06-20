<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\BuyXGetY\Services;

use NiceShoply\Common\Services\Fee\BaseService;

class BxgyFee extends BaseService
{
    public function addFee(): void
    {
        $service = BxgyService::getInstance();
        if (! $service->enabled()) {
            return;
        }

        $result = $service->matchDiscount($this->checkoutService->getCartList());
        if ($result['discount'] <= 0) {
            return;
        }

        $total = -1 * $result['discount'];
        $title = __('BuyXGetY::common.fee_title');
        if (! empty($result['titles'])) {
            $title .= '（'.implode('、', array_unique($result['titles'])).'）';
        }

        $this->checkoutService->addFeeList([
            'code'         => 'bxgy_discount',
            'title'        => $title,
            'total'        => $total,
            'total_format' => currency_format($total),
        ]);
    }
}
