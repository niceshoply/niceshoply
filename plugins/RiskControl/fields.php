<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    ['name' => 'enabled', 'label_key' => 'common.enabled', 'type' => 'switch', 'default' => true],
    // 同 IP 每小时注册次数阈值，超过记录高风险事件
    ['name' => 'ip_register_limit', 'label_key' => 'common.ip_register_limit', 'type' => 'string', 'default' => '5', 'rules' => 'nullable|integer|min:1'],
    // 同 IP 每小时下单次数阈值
    ['name' => 'ip_order_limit', 'label_key' => 'common.ip_order_limit', 'type' => 'string', 'default' => '10', 'rules' => 'nullable|integer|min:1'],
];
