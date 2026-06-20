<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Ewaybill;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.order.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'ewaybill.index',
                'title'           => __('Ewaybill::common.menu'),
                'url'             => console_route('ewaybill.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
