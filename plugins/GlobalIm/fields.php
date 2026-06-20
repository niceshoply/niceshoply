<?php
return [
    ['name' => 'enabled', 'label_key' => 'common.enabled', 'type' => 'select', 'options' => [
        ['value' => '1', 'label_key' => 'common.enabled'], ['value' => '0', 'label_key' => 'common.disabled'],
    ], 'required' => true, 'emptyOption' => false],
    ['name' => 'default_channel', 'label_key' => 'GlobalIm::common.channel', 'type' => 'select', 'options' => [
        ['value' => 'telegram', 'label_key' => 'GlobalIm::common.telegram'],
        ['value' => 'whatsapp', 'label_key' => 'GlobalIm::common.whatsapp'],
    ], 'required' => true, 'emptyOption' => false],
    ['name' => 'telegram_bot_token', 'label_key' => 'GlobalIm::common.telegram_token', 'type' => 'string', 'required' => false],
    ['name' => 'whatsapp_token', 'label_key' => 'GlobalIm::common.whatsapp_token', 'type' => 'string', 'required' => false],
    ['name' => 'whatsapp_phone_id', 'label_key' => 'GlobalIm::common.whatsapp_phone', 'type' => 'string', 'required' => false],
    ['name' => 'webhook_verify_token', 'label_key' => 'GlobalIm::common.verify_token', 'type' => 'string', 'required' => false],
];
