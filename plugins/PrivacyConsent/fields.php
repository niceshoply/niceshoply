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
    ['name' => 'message', 'label_key' => 'common.message', 'type' => 'textarea', 'default' => 'We use cookies to improve your experience. By continuing you agree to our use of cookies.'],
    ['name' => 'accept_label', 'label_key' => 'common.accept_label', 'type' => 'string', 'default' => 'Accept'],
    ['name' => 'reject_label', 'label_key' => 'common.reject_label', 'type' => 'string', 'default' => 'Reject'],
    ['name' => 'policy_label', 'label_key' => 'common.policy_label', 'type' => 'string', 'default' => 'Privacy Policy'],
    ['name' => 'policy_url', 'label_key' => 'common.policy_url', 'type' => 'string', 'required' => false],
    [
        'name'      => 'position',
        'label_key' => 'common.position',
        'type'      => 'select',
        'options'   => [
            ['value' => 'bottom', 'label_key' => 'common.pos_bottom'],
            ['value' => 'top', 'label_key' => 'common.pos_top'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
];
