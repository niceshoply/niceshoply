<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    ['name' => 'mch_id', 'label_key' => 'common.mch_id', 'type' => 'string', 'required' => true, 'rules' => 'required'],
    ['name' => 'mch_cert_serial_no', 'label_key' => 'common.serial_no', 'type' => 'string', 'required' => true, 'rules' => 'required'],
    ['name' => 'mch_private_cert', 'label_key' => 'common.private_cert', 'type' => 'textarea', 'required' => true, 'rules' => 'required'],
    ['name' => 'mch_public_cert', 'label_key' => 'common.public_cert', 'type' => 'textarea', 'required' => true, 'rules' => 'required'],
    ['name' => 'unipay_public_cert', 'label_key' => 'common.platform_cert', 'type' => 'textarea', 'required' => true, 'rules' => 'required'],
    [
        'name'      => 'sandbox',
        'label_key' => 'common.sandbox',
        'type'      => 'select',
        'options'   => [
            ['value' => '0', 'label_key' => 'common.disabled'],
            ['value' => '1', 'label_key' => 'common.enabled'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
];
