<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PrintCenter;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.order.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'print_center.index',
                'title'           => __('PrintCenter::common.menu'),
                'url'             => console_route('print_center.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
