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
    // 订单事件站内信开关
    [
        'name'      => 'notify_on_order',
        'label_key' => 'common.notify_on_order',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.yes'],
            ['value' => '0', 'label_key' => 'common.no'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    // 短信通知
    [
        'name'      => 'enable_sms',
        'label_key' => 'common.enable_sms',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.yes'],
            ['value' => '0', 'label_key' => 'common.no'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    ['name' => 'sms_gateway', 'label_key' => 'common.sms_gateway', 'type' => 'string', 'required' => false],
    ['name' => 'sms_access_key_id', 'label_key' => 'common.sms_access_key_id', 'type' => 'string', 'required' => false],
    ['name' => 'sms_access_key_secret', 'label_key' => 'common.sms_access_key_secret', 'type' => 'string', 'required' => false],
    ['name' => 'sms_sign_name', 'label_key' => 'common.sms_sign_name', 'type' => 'string', 'required' => false],
    // 各事件短信模板ID（如阿里云模板 Code）
    ['name' => 'sms_tpl_order_paid', 'label_key' => 'common.sms_tpl_order_paid', 'type' => 'string', 'required' => false],
    ['name' => 'sms_tpl_order_shipped', 'label_key' => 'common.sms_tpl_order_shipped', 'type' => 'string', 'required' => false],
];
