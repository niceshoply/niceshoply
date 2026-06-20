<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Fee;

use NiceShoply\Common\Services\Member\PointService;

/**
 * 积分抵现费用项（负费用）。
 */
class Points extends BaseService
{
    /**
     * @return void
     */
    public function addFee(): void
    {
        if (! PointService::getInstance()->isEnabled()) {
            return;
        }

        $checkoutData = $this->checkoutService->getCheckoutData();
        $reference    = $checkoutData['reference'] ?? [];
        $pointsToUse  = (int) ($reference['points_to_use'] ?? 0);
        if ($pointsToUse <= 0) {
            return;
        }

        $result = PointService::getInstance()->validateRedeem($this->checkoutService, $pointsToUse);
        if (! $result['valid'] || $result['amount'] <= 0) {
            return;
        }

        $total = -round($result['amount'], 2);

        $this->checkoutService->addFeeList([
            'code'         => 'points',
            'title'        => trans('front/point.fee_title', ['points' => $result['points']]),
            'total'        => $total,
            'total_format' => currency_format($total),
            'reference'    => [
                'points' => $result['points'],
            ],
        ]);
    }
}
