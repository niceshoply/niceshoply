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
    // 首单折扣类型
    [
        'name'      => 'discount_type',
        'label_key' => 'common.discount_type',
        'type'      => 'select',
        'options'   => [
            ['value' => 'fixed', 'label_key' => 'common.type_fixed'],
            ['value' => 'percent', 'label_key' => 'common.type_percent'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    // 折扣值：固定金额或百分比
    [
        'name'      => 'discount_value',
        'label_key' => 'common.discount_value',
        'type'      => 'text',
        'required'  => true,
    ],
    // 折扣门槛（满额可用）
    [
        'name'      => 'min_amount',
        'label_key' => 'common.min_amount',
        'type'      => 'text',
        'required'  => false,
    ],
    // 百分比折扣封顶
    [
        'name'      => 'max_discount',
        'label_key' => 'common.max_discount',
        'type'      => 'text',
        'required'  => false,
    ],
    // 注册欢迎语
    [
        'name'      => 'welcome_message',
        'label_key' => 'common.welcome_message',
        'type'      => 'textarea',
        'required'  => false,
    ],
    // 新人券码（展示在欢迎语中，可对接 Coupon 插件的公共券码）
    [
        'name'      => 'welcome_coupon_code',
        'label_key' => 'common.welcome_coupon_code',
        'type'      => 'text',
        'required'  => false,
    ],
];
