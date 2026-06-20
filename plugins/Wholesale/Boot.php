<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Wholesale;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Wholesale\Services\WholesaleFee;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = WholesaleFee::class;

            return $classes;
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'wholesale.index',
                'title'           => __('Wholesale::common.menu'),
                'url'             => console_route('wholesale.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
