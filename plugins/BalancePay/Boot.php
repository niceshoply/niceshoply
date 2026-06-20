<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\BalancePay;

class Boot
{
    public function init(): void
    {
        // 余额支付作为 billing 类型支付方式自动注册，支付视图为 BalancePay::payment，无需额外钩子。
    }
}
