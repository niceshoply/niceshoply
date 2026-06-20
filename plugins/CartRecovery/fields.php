<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    [
        'name'      => 'enabled',
        'label_key' => 'common.enabled',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.yes'],
            ['value' => '0', 'label_key' => 'common.no'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    // 弃购判定：购物车闲置小时数
    [
        'name'      => 'idle_hours',
        'label_key' => 'common.idle_hours',
        'type'      => 'text',
        'required'  => true,
    ],
    // 同一会员两次召回的最小间隔天数（防打扰）
    [
        'name'      => 'cooldown_days',
        'label_key' => 'common.cooldown_days',
        'type'      => 'text',
        'required'  => true,
    ],
    // 召回券码（展示在召回消息中）
    [
        'name'      => 'recovery_coupon_code',
        'label_key' => 'common.recovery_coupon_code',
        'type'      => 'text',
        'required'  => false,
    ],
];
