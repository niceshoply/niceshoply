<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers\Account;

use App\Http\Controllers\Controller;
use NiceShoply\Common\Models\Address;
use NiceShoply\Common\Models\Customer\Favorite;
use NiceShoply\Common\Models\Order;

class AccountController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $customer   = current_customer();
        $customerID = $customer->id;
        $data       = [
            'customer'      => $customer,
            'order_total'   => Order::query()->where('customer_id', $customerID)->count(),
            'fav_total'     => Favorite::query()->where('customer_id', $customerID)->count(),
            'address_total' => Address::query()->where('customer_id', $customerID)->count(),
            'latest_orders' => Order::query()->where('customer_id', $customerID)->orderByDesc('id')->limit(3)->get(),
        ];

        return nice_view('account.home', $data);
    }
}
