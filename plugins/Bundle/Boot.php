<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bundle;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Bundle\Services\BundleFee;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = BundleFee::class;

            return $classes;
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'bundle.index',
                'title'           => __('Bundle::common.menu'),
                'url'             => console_route('bundle.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
