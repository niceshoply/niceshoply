<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Fee;

use NiceShoply\Common\Services\Promotion\PromotionService;

/**
 * 促销折扣费用项。
 *
 * 把促销引擎计算出的折扣以「负费用项」追加进结账 feeList，
 * 从而自动进入金额闭环（getAmount 求和），并在落单时写入订单费用明细。
 */
class Discount extends BaseService
{
    /**
     * @return void
     */
    public function addFee(): void
    {
        $entries = PromotionService::getInstance()->calculate($this->checkoutService);

        foreach ($entries as $entry) {
            $amount = (float) ($entry['amount'] ?? 0);

            // 免运费类活动不抵小计（已通过 markFreeShipping 标记），仅记录便于落单流水
            if ($amount <= 0) {
                if (! empty($entry['free_shipping'])) {
                    $this->checkoutService->recordAppliedDiscount($entry);
                }

                continue;
            }

            $total = -round($amount, 2);

            $this->checkoutService->addFeeList([
                'code'         => 'discount',
                'title'        => $entry['label'] ?? trans('front/coupon.discount'),
                'total'        => $total,
                'total_format' => currency_format($total),
            ]);

            $this->checkoutService->recordAppliedDiscount($entry);
        }
    }
}
