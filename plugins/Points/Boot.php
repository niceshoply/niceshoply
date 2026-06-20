<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Points;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Points\Services\PointService;
use Plugin\Points\Services\PointsFee;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 积分抵现作为费用项
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = PointsFee::class;

            return $classes;
        });

        // 下单后置：扣除已用积分 + 赠送积分
        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            PointService::getInstance()->handleOrderConfirmed($data['order'] ?? null, $data['checkout'] ?? []);
        });

        // 后台营销菜单
        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'points.index',
                'title'           => __('Points::common.menu_title'),
                'url'             => console_route('points.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
