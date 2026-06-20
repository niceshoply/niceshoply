<?php
return [
    ['name' => 'enabled', 'label_key' => 'common.enabled', 'type' => 'select', 'options' => [
        ['value' => '1', 'label_key' => 'common.enabled'], ['value' => '0', 'label_key' => 'common.disabled'],
    ], 'required' => true, 'emptyOption' => false],
    ['name' => 'apply_on_shipping', 'label_key' => 'TaxEngine::common.apply_on_shipping', 'type' => 'select', 'options' => [
        ['value' => '0', 'label_key' => 'common.disabled'], ['value' => '1', 'label_key' => 'common.enabled'],
    ], 'required' => true, 'emptyOption' => false],
];
