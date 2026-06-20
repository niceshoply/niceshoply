<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\NotifyCenter;

use Plugin\NotifyCenter\Services\NotifyService;

class Boot
{
    public function init(): void
    {
        if (! (bool) plugin_setting('notify_center', 'enabled', true)) {
            return;
        }

        // 下单
        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            NotifyService::getInstance()->handleOrderEvent($data['order'] ?? null, 'placed');
        });

        // 订单状态变更（付款/发货等）
        listen_hook_action('service.state_machine.change_status.after', function (array $data) {
            $status = (string) ($data['status'] ?? '');
            $event  = match ($status) {
                'paid'    => 'paid',
                'shipped' => 'shipped',
                default   => '',
            };
            if ($event !== '') {
                NotifyService::getInstance()->handleOrderEvent($data['order'] ?? null, $event);
            }
        });

        // 后台菜单（设置侧栏）
        listen_hook_filter('console.component.sidebar.setting.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'notifications.index',
                'title'           => __('NotifyCenter::common.menu_title'),
                'url'             => console_route('notifications.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
