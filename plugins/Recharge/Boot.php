<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Recharge;

use NiceShoply\Common\Services\StateMachineService;
use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Recharge\Services\RechargeService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 订单状态变为已支付时，若为充值订单则入账余额
        listen_hook_action('service.state_machine.change_status.after', function (array $data) {
            $status = (string) ($data['status'] ?? '');
            if ($status !== StateMachineService::PAID) {
                return;
            }
            RechargeService::getInstance()->handleOrderPaid($data['order'] ?? null);
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'recharge.plans',
                'title'           => __('Recharge::common.menu_plans'),
                'url'             => console_route('recharge.plans'),
                'skip_permission' => true,
            ];
            $routes[] = [
                'route'           => 'recharge.records',
                'title'           => __('Recharge::common.menu_records'),
                'url'             => console_route('recharge.records'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
