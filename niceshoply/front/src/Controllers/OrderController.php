<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Order;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Front\Services\PaymentService;

class OrderController extends Controller
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function pay(Request $request): mixed
    {
        try {
            $order = Order::query()->where('number', $request->number)->firstOrFail();

            return PaymentService::getInstance($order)->pay();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Order detail
     *
     * @param  int  $number
     * @return mixed
     */
    public function numberShow(int $number): mixed
    {
        $order = OrderRepo::getInstance()->getOrderByNumber($number);
        $order->load(['items', 'fees']);
        $data = [
            'order' => $order,
        ];

        return nice_view('orders.show', $data);
    }
}
