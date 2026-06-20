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
    // 每日基础签到积分
    [
        'name'      => 'base_points',
        'label_key' => 'common.base_points',
        'type'      => 'text',
        'required'  => true,
    ],
    // 连续签到阶梯奖励：格式「连续天数:额外积分」逗号分隔，如 7:20,30:100
    [
        'name'      => 'streak_bonus',
        'label_key' => 'common.streak_bonus',
        'type'      => 'text',
        'required'  => false,
    ],
];
