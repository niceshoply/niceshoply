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
    // 每消费 1（货币单位）获得多少积分
    ['name' => 'earn_per_unit', 'label_key' => 'common.earn_per_unit', 'type' => 'string', 'required' => false],
    // 多少积分抵 1（货币单位）
    ['name' => 'points_per_unit', 'label_key' => 'common.points_per_unit', 'type' => 'string', 'required' => false],
    // 单笔最多抵扣订单小计的百分比（0-100）
    ['name' => 'max_redeem_ratio', 'label_key' => 'common.max_redeem_ratio', 'type' => 'string', 'required' => false],
];
