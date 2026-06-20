<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Recharge\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\RestAPI\FrontApiControllers\BaseController;
use Plugin\Recharge\Models\RechargeOrder;
use Plugin\Recharge\Services\RechargeService;

class RechargeController extends BaseController
{
    public function plans(): mixed
    {
        return json_success('ok', [
            'plans'        => RechargeService::getInstance()->activePlans(),
            'allow_custom' => (bool) plugin_setting('recharge', 'allow_custom', false),
        ]);
    }

    public function create(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'plan_id'             => 'nullable|integer|min:1',
                'amount'              => 'nullable|numeric|min:0.01',
                'billing_method_code' => 'required|string|max:64',
                'billing_method_name' => 'nullable|string|max:128',
            ]);

            $order = RechargeService::getInstance()->createRechargeOrder(
                (int) token_customer_id(),
                $data['plan_id'] ?? null,
                (float) ($data['amount'] ?? 0),
                $data['billing_method_code'],
                $data['billing_method_name'] ?? ''
            );

            return json_success(__('Recharge::common.order_created'), [
                'order_id'     => $order->id,
                'order_number' => $order->number,
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function myRecords(): mixed
    {
        $records = RechargeOrder::query()
            ->where('customer_id', (int) token_customer_id())
            ->orderByDesc('id')
            ->paginate(20);

        return json_success('ok', $records);
    }
}
