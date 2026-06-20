<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\NewCustomer;

use NiceShoply\Plugin\Core\BaseBoot;
use Plugin\NewCustomer\Services\NewCustomerFee;
use Plugin\NewCustomer\Services\NewCustomerService;

class Boot extends BaseBoot
{
    public function init(): void
    {
        // 1) 首单立减作为结算费用项注入
        listen_hook_filter('service.checkout.fee.methods', function (array $classes) {
            $classes[] = NewCustomerFee::class;

            return $classes;
        });

        // 2) 注册成功后发送欢迎站内信（含新人券码）
        listen_hook_action('front.service.account.register', function ($customer) {
            if (! (bool) plugin_setting('new_customer', 'enabled', true)) {
                return;
            }
            NewCustomerService::getInstance()->welcome($customer);
        });
    }
}
