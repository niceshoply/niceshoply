<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use NiceShoply\Console\Repositories\Analytics\CustomerRepo;
use NiceShoply\Console\Repositories\Analytics\ProductRepo;
use NiceShoply\Console\Repositories\Dashboard\OrderRepo;

class AnalyticsController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $data = [
            'order_latest_week'    => OrderRepo::getInstance()->getOrderCountLatestWeek(),
            'product_latest_week'  => ProductRepo::getInstance()->getProductCountLatestWeek(),
            'customer_latest_week' => CustomerRepo::getInstance()->getCustomerCountLatestWeek(),
        ];

        return nice_view('console::analytics.index', $data);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function order(): mixed
    {
        $data = [
            'order_quantity_latest_month' => OrderRepo::getInstance()->getOrderCountLatestMonth(),
            'order_quantity_latest_week'  => OrderRepo::getInstance()->getOrderCountLatestWeek(),
            'order_total_latest_month'    => \NiceShoply\Console\Repositories\Analytics\OrderRepo::getInstance()->getOrderTotalLatestMonth(),
            'order_total_latest_week'     => \NiceShoply\Console\Repositories\Analytics\OrderRepo::getInstance()->getOrderTotalLatestWeek(),
            'top_sale_products'           => \NiceShoply\Console\Repositories\Dashboard\ProductRepo::getInstance()->getTopSaleProducts(),        ];

        return nice_view('console::analytics.order', $data);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function product(): mixed
    {
        $data = [
            'product_latest_week' => ProductRepo::getInstance()->getProductCountLatestWeek(),
        ];

        return nice_view('console::analytics.product', $data);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function customer(): mixed
    {
        $data = [
            'customer_latest_week' => CustomerRepo::getInstance()->getCustomerCountLatestWeek(),
            'customer_source'      => CustomerRepo::getInstance()->getCustomerSourceData(),
        ];

        return nice_view('console::analytics.customer', $data);
    }
}
