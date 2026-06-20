<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Bargain;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Bargain\Services\BargainFee;
use Plugin\Bargain\Services\BargainService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = BargainFee::class;

            return $classes;
        });

        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            BargainService::getInstance()->handleOrderConfirmed($data['order'] ?? null, $data['checkout'] ?? []);
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'bargain_activities.index',
                'title'           => __('Bargain::common.menu_title'),
                'url'             => console_route('bargain_activities.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
