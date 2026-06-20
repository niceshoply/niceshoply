<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Cod;

class Boot
{
    public function init(): void
    {
        // 货到付款作为 billing 类型支付方式自动注册，支付视图为 Cod::payment。
        // 订单保持 unpaid，由商家收货后在后台标记收款，无需在线支付钩子。
    }
}
