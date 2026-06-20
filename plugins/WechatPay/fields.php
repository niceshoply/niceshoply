<?php

return [
    [
        'name'        => 'app_id',
        'label_key'   => 'WechatPay::common.app_id',
        'type'        => 'string',
        'required'    => true,
        'description' => '微信开放平台 AppID',
    ],
    [
        'name'        => 'mch_id',
        'label_key'   => 'WechatPay::common.mch_id',
        'type'        => 'string',
        'required'    => true,
        'description' => '微信支付商户号',
    ],
    [
        'name'        => 'api_key',
        'label_key'   => 'WechatPay::common.api_key',
        'type'        => 'string',
        'required'    => true,
        'description' => 'API v2 密钥或 v3 密钥',
    ],
    [
        'name'        => 'sandbox_mode',
        'label_key'   => 'WechatPay::common.sandbox_mode',
        'type'        => 'bool',
        'required'    => false,
        'description' => '沙箱模式',
    ],
];
