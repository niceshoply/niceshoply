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
    // 一级/二级佣金比例(%)
    ['name' => 'level1_rate', 'label_key' => 'common.level1_rate', 'type' => 'string', 'required' => false],
    ['name' => 'level2_rate', 'label_key' => 'common.level2_rate', 'type' => 'string', 'required' => false],
    // 佣金计算基数：subtotal(商品小计) / total(订单金额)
    [
        'name'      => 'commission_base',
        'label_key' => 'common.commission_base',
        'type'      => 'select',
        'options'   => [
            ['value' => 'subtotal', 'label_key' => 'common.base_subtotal'],
            ['value' => 'total', 'label_key' => 'common.base_total'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    // 是否允许自主成为推广员
    [
        'name'      => 'self_apply',
        'label_key' => 'common.self_apply',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.yes'],
            ['value' => '0', 'label_key' => 'common.no'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
];
