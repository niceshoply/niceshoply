<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailMarketing;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'email_marketing.campaigns',
                'title'           => __('EmailMarketing::common.menu_campaigns'),
                'url'             => console_route('email_marketing.campaigns'),
                'skip_permission' => true,
            ];
            $routes[] = [
                'route'           => 'email_marketing.subscribers',
                'title'           => __('EmailMarketing::common.menu_subscribers'),
                'url'             => console_route('email_marketing.subscribers'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
