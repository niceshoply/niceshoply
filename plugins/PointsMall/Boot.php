<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PointsMall;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'points_mall.items',
                'title'           => __('PointsMall::common.menu_items'),
                'url'             => console_route('points_mall.items'),
                'skip_permission' => true,
            ];
            $routes[] = [
                'route'           => 'points_mall.redemptions',
                'title'           => __('PointsMall::common.menu_redemptions'),
                'url'             => console_route('points_mall.redemptions'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
