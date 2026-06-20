<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PointsMall\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\PointsMall\Models\Redemption;
use Plugin\PointsMall\Services\PointsMallService;

class PointsMallController extends BaseController
{
    public function index(): mixed
    {
        return json_success('ok', PointsMallService::getInstance()->listActive());
    }

    public function myRedemptions(): mixed
    {
        $list = Redemption::query()
            ->where('customer_id', (int) token_customer_id())
            ->orderByDesc('id')
            ->paginate(20);

        return json_success('ok', $list);
    }

    public function redeem(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'item_id'  => 'required|integer|min:1',
                'quantity' => 'nullable|integer|min:1',
                'contact'  => 'nullable|string|max:191',
            ]);

            $redemption = PointsMallService::getInstance()->redeem(
                (int) token_customer_id(),
                (int) $data['item_id'],
                (int) ($data['quantity'] ?? 1),
                $data['contact'] ?? ''
            );

            return json_success(__('PointsMall::common.redeem_success'), ['number' => $redemption->number]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
