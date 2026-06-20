<?php
return [
    ['name' => 'provider', 'label_key' => 'GlobalPay::common.provider', 'type' => 'select', 'options' => [
        ['value' => 'stripe', 'label_key' => 'GlobalPay::common.stripe'],
        ['value' => 'paypal', 'label_key' => 'GlobalPay::common.paypal'],
    ], 'required' => true, 'emptyOption' => false],
    ['name' => 'stripe_secret', 'label_key' => 'GlobalPay::common.stripe_secret', 'type' => 'string', 'required' => false],
    ['name' => 'stripe_webhook_secret', 'label_key' => 'GlobalPay::common.stripe_webhook', 'type' => 'string', 'required' => false],
    ['name' => 'paypal_client_id', 'label_key' => 'GlobalPay::common.paypal_client', 'type' => 'string', 'required' => false],
    ['name' => 'paypal_secret', 'label_key' => 'GlobalPay::common.paypal_secret', 'type' => 'string', 'required' => false],
    ['name' => 'sandbox', 'label_key' => 'common.sandbox', 'type' => 'select', 'options' => [
        ['value' => '0', 'label_key' => 'common.disabled'], ['value' => '1', 'label_key' => 'common.enabled'],
    ], 'required' => true, 'emptyOption' => false],
];
