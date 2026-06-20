<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Distribution\Services\DistributionService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        if (! (bool) plugin_setting('distribution', 'enabled', true)) {
            return;
        }

        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            DistributionService::getInstance()->handleOrderConfirmed($data['order'] ?? null);
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'distribution.commissions',
                'title'           => __('Distribution::common.menu_title'),
                'url'             => console_route('distribution.commissions'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
