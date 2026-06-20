<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Repositories;

use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Models\Product;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Services\StateMachineService;

class DashboardRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public function getCards(): array
    {
        $filters = [
            'statuses' => StateMachineService::getValidStatuses(),
        ];

        $validOrderBuilder = OrderRepo::getInstance()->builder($filters);

        return [
            [
                'title'    => console_trans('dashboard.order_quantity'),
                'icon'     => 'bi bi-cart',
                'quantity' => $validOrderBuilder->count(),
                'url'      => console_route('orders.index'),
            ],
            [
                'title'    => console_trans('dashboard.product_quantity'),
                'icon'     => 'bi bi-bag',
                'quantity' => Product::query()->count(),
                'url'      => console_route('products.index'),
            ],
            [
                'title'    => console_trans('dashboard.customer_quantity'),
                'icon'     => 'bi bi-person',
                'quantity' => Customer::query()->count(),
                'url'      => console_route('customers.index'),
            ],
            [
                'title'    => console_trans('dashboard.order_amount'),
                'icon'     => 'bi bi-gem',
                'quantity' => currency_format($validOrderBuilder->sum('total')),
                'url'      => console_route('orders.index'),
            ],
        ];
    }
}
