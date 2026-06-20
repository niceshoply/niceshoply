<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductIo;

use NiceShoply\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('console.component.sidebar.product.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'product_io.index',
                'title'           => __('ProductIo::common.menu'),
                'url'             => console_route('product_io.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
