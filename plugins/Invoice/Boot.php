<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Invoice;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.setting.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'invoices.index',
                'title'           => __('Invoice::common.menu_invoices'),
                'url'             => console_route('invoices.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
