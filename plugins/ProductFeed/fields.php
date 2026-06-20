<?php
return [
    ['name' => 'enabled', 'label_key' => 'common.enabled', 'type' => 'select', 'options' => [
        ['value' => '1', 'label_key' => 'common.enabled'], ['value' => '0', 'label_key' => 'common.disabled'],
    ], 'required' => true, 'emptyOption' => false],
    ['name' => 'default_channel', 'label_key' => 'ProductFeed::common.channel', 'type' => 'select', 'options' => [
        ['value' => 'google', 'label_key' => 'ProductFeed::common.google'],
        ['value' => 'meta', 'label_key' => 'ProductFeed::common.meta'],
        ['value' => 'csv', 'label_key' => 'ProductFeed::common.csv'],
    ], 'required' => true, 'emptyOption' => false],
    ['name' => 'base_url', 'label_key' => 'ProductFeed::common.base_url', 'type' => 'string', 'required' => false],
];
