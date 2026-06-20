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
            ['value' => 'recaptcha', 'label_key' => 'common.recaptcha'],
            ['value' => 'turnstile', 'label_key' => 'common.turnstile'],
            ['value' => 'hcaptcha', 'label_key' => 'common.hcaptcha'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    ['name' => 'site_key', 'label_key' => 'common.site_key', 'type' => 'string', 'required' => true, 'rules' => 'required'],
    ['name' => 'secret_key', 'label_key' => 'common.secret_key', 'type' => 'string', 'required' => true, 'rules' => 'required'],
];
