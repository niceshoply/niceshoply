<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MarketingFlow;

use NiceShoply\Common\Services\StateMachineService;
use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\MarketingFlow\Services\MarketingFlowService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 注册成功
        listen_hook_action('front.service.account.register', function ($customer) {
            MarketingFlowService::getInstance()->trigger('register', (int) ($customer->id ?? 0));
        });

        // 下单成功
        listen_hook_action('service.checkout.confirm.after', function (array $data) {
            $order = $data['order'] ?? ($data['checkout']['order'] ?? null);
            if ($order) {
                MarketingFlowService::getInstance()->trigger('order_placed', (int) $order->customer_id, [
                    'order_no' => (string) $order->number,
                ]);
            }
        });

        // 支付成功
        listen_hook_action('service.state_machine.change_status.after', function (array $data) {
            $order = $data['order'] ?? null;
            $status = $data['status'] ?? ($data['to'] ?? null);
            if ($order && $status === StateMachineService::PAID) {
                MarketingFlowService::getInstance()->trigger('order_paid', (int) $order->customer_id, [
                    'order_no' => (string) $order->number,
                ]);
            }
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'marketing_flow.index',
                'title'           => __('MarketingFlow::common.menu'),
                'url'             => console_route('marketing_flow.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
