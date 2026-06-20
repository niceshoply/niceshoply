<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReturnLogistics\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\ReturnLogistics\Models\ReturnShipment;
use Plugin\ReturnLogistics\Services\ReturnLogisticsService;
use Throwable;

class ReturnController extends BaseController
{
    public function info(int $aftersaleId): mixed
    {
        $ship = ReturnShipment::query()->where('aftersale_id', $aftersaleId)->first();
        if (! $ship) {
            return json_fail(__('ReturnLogistics::common.not_found'));
        }

        return json_success('ok', ReturnLogisticsService::getInstance()->presentShipment($ship));
    }

    public function submitTracking(Request $request, int $aftersaleId): mixed
    {
        try {
            $data = $request->validate([
                'shipper_code' => 'required|string|max:16',
                'tracking_no'  => 'required|string|max:64',
            ]);
            $customerId = (int) (token_customer_id() ?? 0);
            $ship = ReturnLogisticsService::getInstance()->submitTracking(
                $aftersaleId,
                $customerId,
                $data['shipper_code'],
                $data['tracking_no']
            );

            return json_success(__('ReturnLogistics::common.submitted'), ReturnLogisticsService::getInstance()->presentShipment($ship));
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }
}
