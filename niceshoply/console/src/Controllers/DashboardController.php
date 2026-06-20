<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use NiceShoply\Console\Repositories\Dashboard\OrderRepo;
use NiceShoply\Console\Repositories\Dashboard\ProductRepo;
use NiceShoply\Console\Repositories\DashboardRepo;

class DashboardController extends BaseController
{
    /**
     * Dashboard for console home page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function index(): mixed
    {
        $data = [
            'cards' => DashboardRepo::getInstance()->getCards(),
            'order' => [
                'latest_week' => OrderRepo::getInstance()->getOrderCountLatestWeek(),
            ],
            'top_sale_products' => ProductRepo::getInstance()->getTopSaleProducts(),
        ];

        return nice_view('console::dashboard', $data);
    }
}
