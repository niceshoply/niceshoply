<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    ['name' => 'kdniao_ebusiness_id', 'label_key' => 'common.ebusiness_id', 'type' => 'string', 'required' => false],
    ['name' => 'kdniao_api_key', 'label_key' => 'common.api_key', 'type' => 'string', 'required' => false],
    [
        'name' => 'sandbox', 'label_key' => 'common.sandbox', 'type' => 'select',
        'options' => [
            ['value' => '0', 'label_key' => 'common.disabled'],
            ['value' => '1', 'label_key' => 'common.enabled'],
        ],
        'required' => true, 'emptyOption' => false,
    ],
    // 寄件人模板
    ['name' => 'sender_name', 'label_key' => 'common.sender_name', 'type' => 'string', 'required' => false],
    ['name' => 'sender_mobile', 'label_key' => 'common.sender_mobile', 'type' => 'string', 'required' => false],
    ['name' => 'sender_province', 'label_key' => 'common.sender_province', 'type' => 'string', 'required' => false],
    ['name' => 'sender_city', 'label_key' => 'common.sender_city', 'type' => 'string', 'required' => false],
    ['name' => 'sender_area', 'label_key' => 'common.sender_area', 'type' => 'string', 'required' => false],
    ['name' => 'sender_address', 'label_key' => 'common.sender_address', 'type' => 'string', 'required' => false],
];
