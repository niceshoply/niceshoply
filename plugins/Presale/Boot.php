<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Presale;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Presale\Services\PresaleFee;
use Plugin\Presale\Services\PresaleService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = PresaleFee::class;

            return $classes;
        });

        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            $cartList = $data['checkout']['cart_list'] ?? ($data['cart_list'] ?? []);
            PresaleService::getInstance()->handleOrderConfirmed($cartList);
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'presale.index',
                'title'           => __('Presale::common.menu'),
                'url'             => console_route('presale.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
