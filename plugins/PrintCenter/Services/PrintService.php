<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PrintCenter\Services;

use NiceShoply\Common\Models\Order;

class PrintService
{
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @param  int[]  $orderIds
     */
    public function orders(array $orderIds): array
    {
        return Order::query()
            ->with(['items'])
            ->whereIn('id', $orderIds)
            ->orderBy('id')
            ->get()
            ->all();
    }

    public function shopName(): string
    {
        return (string) plugin_setting('print_center', 'shop_name', 'NiceShoply');
    }

    public function shopAddress(): string
    {
        return (string) plugin_setting('print_center', 'shop_address', '');
    }
}
