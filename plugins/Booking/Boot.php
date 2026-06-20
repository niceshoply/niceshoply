<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Booking;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.order.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'booking.bookings',
                'title'           => __('Booking::common.menu_bookings'),
                'url'             => console_route('booking.bookings'),
                'skip_permission' => true,
            ];
            $routes[] = [
                'route'           => 'booking.services',
                'title'           => __('Booking::common.menu_services'),
                'url'             => console_route('booking.services'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
