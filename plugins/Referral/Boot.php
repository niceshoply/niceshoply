<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Referral;

use NiceShoply\Common\Services\StateMachineService;
use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\Referral\Services\ReferralService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        listen_hook_action('front.service.account.register', function ($customer) {
            ReferralService::getInstance()->onRegister($customer);
        });

        listen_hook_action('service.state_machine.change_status.after', function (array $data) {
            $order  = $data['order'] ?? null;
            $status = $data['status'] ?? null;
            if ($order && $status === StateMachineService::PAID) {
                ReferralService::getInstance()->onFirstOrderPaid($order);
            }
        });

        listen_hook_filter('console.component.sidebar.marketing.routes', function (array $routes) {
            $routes[] = [
                'route'           => 'referral.index',
                'title'           => __('Referral::common.menu'),
                'url'             => console_route('referral.index'),
                'skip_permission' => true,
            ];

            return $routes;
        });
    }
}
