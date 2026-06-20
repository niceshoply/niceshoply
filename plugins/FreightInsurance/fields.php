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
    [
        'name'      => 'mode',
        'label_key' => 'common.mode',
        'type'      => 'select',
        'options'   => [
            ['value' => 'fixed', 'label_key' => 'common.mode_fixed'],
            ['value' => 'percent', 'label_key' => 'common.mode_percent'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    // 固定保费金额 或 百分比(%)
    ['name' => 'value', 'label_key' => 'common.value', 'type' => 'string', 'required' => true, 'rules' => 'required|numeric'],
    // 百分比模式下的保费上下限（0 不限）
    ['name' => 'min_premium', 'label_key' => 'common.min_premium', 'type' => 'string', 'required' => false],
    ['name' => 'max_premium', 'label_key' => 'common.max_premium', 'type' => 'string', 'required' => false],
];
