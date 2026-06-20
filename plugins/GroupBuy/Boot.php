<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\GroupBuy;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\GroupBuy\Services\GroupBuyFee;
use Plugin\GroupBuy\Services\GroupBuyService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = GroupBuyFee::class;

            return $classes;
        });

        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            GroupBuyService::getInstance()->handleOrderConfirmed($data['order'] ?? null, $data['checkout'] ?? []);
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'group_buy_activities.index',
                'title'           => __('GroupBuy::common.menu_title'),
                'url'             => console_route('group_buy_activities.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
