<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FreightInsurance;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\FreightInsurance\Services\FreightInsuranceFee;
use Plugin\FreightInsurance\Services\FreightInsuranceService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = FreightInsuranceFee::class;

            return $classes;
        });

        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            $order = $data['order'] ?? ($data['checkout']['order'] ?? null);
            FreightInsuranceService::getInstance()->recordOrder($order);
        });

        listen_hook_filter('console.component.sidebar.order.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'freight_insurance.index',
                'title'           => __('FreightInsurance::common.menu'),
                'url'             => console_route('freight_insurance.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
