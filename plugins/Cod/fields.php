<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    // 货到付款页面提示文案
    [
        'name'      => 'notice',
        'label_key' => 'common.notice',
        'type'      => 'textarea',
        'required'  => false,
    ],
    // 可用金额上限（0 不限）
    [
        'name'      => 'max_amount',
        'label_key' => 'common.max_amount',
        'type'      => 'string',
        'required'  => false,
    ],
];
