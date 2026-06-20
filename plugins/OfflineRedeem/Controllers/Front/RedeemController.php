<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\OfflineRedeem\Controllers\Front;

use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\OfflineRedeem\Services\RedeemService;
use Throwable;

class RedeemController extends BaseController
{
    public function verify(Request $request): mixed
    {
        try {
            $code = (string) $request->input('code', '');
            $row  = RedeemService::getInstance()->verify($code);

            return json_success('ok', ['code' => $row->code, 'title' => $row->title, 'type' => $row->type, 'status' => $row->status]);
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }

    public function redeem(Request $request): mixed
    {
        try {
            $data = $request->validate(['code' => 'required|string', 'staff_token' => 'nullable|string']);
            $row = RedeemService::getInstance()->redeem($data['code'], $data['staff_token'] ?? '');

            return json_success(__('OfflineRedeem::common.redeemed_ok'), ['code' => $row->code, 'title' => $row->title]);
        } catch (Throwable $e) {
            return json_fail($e->getMessage());
        }
    }
}
