<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\BuyXGetY;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\BuyXGetY\Services\BxgyFee;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = BxgyFee::class;

            return $classes;
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'buy_x_get_y.index',
                'title'           => __('BuyXGetY::common.menu'),
                'url'             => console_route('buy_x_get_y.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
