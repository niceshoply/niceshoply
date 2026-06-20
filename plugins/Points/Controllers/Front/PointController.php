<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Points\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Services\CheckoutService;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Points\Models\PointLog;
use Plugin\Points\Services\PointService;

class PointController extends BaseController
{
    public function balance(): mixed
    {
        $customerId = (int) token_customer_id();

        return json_success('ok', [
            'balance'         => PointService::getInstance()->balance($customerId),
            'points_per_unit' => (float) plugin_setting('points', 'points_per_unit', 0),
        ]);
    }

    public function logs(): mixed
    {
        $customerId = (int) token_customer_id();
        $logs = PointLog::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('id')
            ->paginate(20);

        return json_success('ok', $logs);
    }

    /**
     * 设置本次结算使用的积分数，写入 checkout.reference.use_points。
     */
    public function use(Request $request): mixed
    {
        try {
            $points     = max((int) $request->get('points', 0), 0);
            $customerId = (int) token_customer_id();
            $checkout   = CheckoutService::getInstance($customerId);

            $checkoutData             = $checkout->getCheckoutData();
            $reference                = $checkoutData['reference'] ?? [];
            $reference['use_points']  = $points;
            $checkout->updateValues(['reference' => $reference]);

            $subtotal = (float) collect($checkout->getCartList())->sum('subtotal');
            $result   = PointService::getInstance()->computeRedeem($customerId, $points, $subtotal);

            return json_success(__('Points::common.applied'), [
                'used_points' => $result['points'],
                'discount'    => $result['discount'],
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
