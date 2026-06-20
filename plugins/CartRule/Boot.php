<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\CartRule;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\CartRule\Services\CartRuleFee;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = CartRuleFee::class;

            return $classes;
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'cart_rules.index',
                'title'           => __('CartRule::common.menu_title'),
                'url'             => console_route('cart_rules.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
