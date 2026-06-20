<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Membership;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Membership\Services\MembershipFee;
use Plugin\Membership\Services\MembershipService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = MembershipFee::class;

            return $classes;
        });

        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            MembershipService::getInstance()->handleOrderConfirmed($data['order'] ?? null);
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'membership_levels.index',
                'title'           => __('Membership::common.menu_title'),
                'url'             => console_route('membership_levels.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
