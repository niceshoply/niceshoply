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
        'name'      => 'provider',
        'label_key' => 'common.provider',
        'type'      => 'select',
        'options'   => [
            ['value' => 'kdniao', 'label_key' => 'common.provider_kdniao'],
            ['value' => 'kuaidi100', 'label_key' => 'common.provider_kuaidi100'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    // 快递鸟：ebusiness_id + api_key
    ['name' => 'kdniao_ebusiness_id', 'label_key' => 'common.kdniao_ebusiness_id', 'type' => 'string', 'required' => false],
    ['name' => 'kdniao_api_key', 'label_key' => 'common.kdniao_api_key', 'type' => 'string', 'required' => false],
    // 快递100：customer + key
    ['name' => 'kuaidi100_customer', 'label_key' => 'common.kuaidi100_customer', 'type' => 'string', 'required' => false],
    ['name' => 'kuaidi100_key', 'label_key' => 'common.kuaidi100_key', 'type' => 'string', 'required' => false],
    [
        'name'      => 'sandbox',
        'label_key' => 'common.sandbox',
        'type'      => 'select',
        'options'   => [
            ['value' => '0', 'label_key' => 'common.no'],
            ['value' => '1', 'label_key' => 'common.yes'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
];
