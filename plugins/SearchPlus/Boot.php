<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SearchPlus;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.setting.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'search_plus.index',
                'title'           => __('SearchPlus::common.menu'),
                'url'             => console_route('search_plus.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
