<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\BalancePay\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\OrderRepo;
use Plugin\BalancePay\Services\BalancePayService;
use Throwable;

class BalancePayController extends Controller
{
    public function confirm(Request $request)
    {
        $orderNumber = (string) $request->input('order_number');
        $order       = OrderRepo::getInstance()->getOrderByNumber($orderNumber);

        if (! $order) {
            return back()->with('error', __('BalancePay::common.order_not_found'));
        }

        $customer   = current_customer();
        $customerId = (int) ($customer->id ?? 0);

        try {
            BalancePayService::getInstance()->pay($order, $customerId);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->to(front_route('payment.success').'?order_number='.$order->number);
    }
}
