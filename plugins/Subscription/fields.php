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
        'name'        => 'default_payment_mode',
        'label_key'   => 'common.default_payment_mode',
        'type'        => 'select',
        'options'     => [
            ['value' => 'reminder', 'label_key' => 'common.mode_reminder'],
            ['value' => 'auto_balance', 'label_key' => 'common.mode_auto_balance'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
];
