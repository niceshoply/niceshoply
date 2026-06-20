<?php

return [
    [
        'name'        => 'app_id',
        'label_key'   => 'Alipay::common.app_id',
        'type'        => 'string',
        'required'    => true,
    ],
    [
        'name'        => 'private_key',
        'label_key'   => 'Alipay::common.private_key',
        'type'        => 'textarea',
        'required'    => true,
    ],
    [
        'name'        => 'alipay_public_key',
        'label_key'   => 'Alipay::common.alipay_public_key',
        'type'        => 'textarea',
        'required'    => true,
    ],
    [
        'name'        => 'sandbox_mode',
        'label_key'   => 'Alipay::common.sandbox_mode',
        'type'        => 'bool',
        'required'    => false,
    ],
];
