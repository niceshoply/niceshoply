<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\RiskControl;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\RiskControl\Services\RiskControlService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 注册风控
        listen_hook_action('front.service.account.register', function ($customer) {
            RiskControlService::getInstance()->evaluateRegister(
                $customer->email ?? null,
                request()->ip()
            );
        });

        // 下单风控
        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            $order = $data['order'] ?? ($data['checkout']['order'] ?? null);
            RiskControlService::getInstance()->evaluateOrder($order, request()->ip());
        });

        // 后台菜单（事件 + 黑名单）
        listen_hook_filter('console.component.sidebar.setting.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'risk_control.events',
                'title'           => __('RiskControl::common.menu_events'),
                'url'             => console_route('risk_control.events'),
                'skip_permission' => true,
            ];
            $routes[] = [
                'route'           => 'risk_control.blacklist',
                'title'           => __('RiskControl::common.menu_blacklist'),
                'url'             => console_route('risk_control.blacklist'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
