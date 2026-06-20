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
        'name'      => 'enabled',
        'label_key' => 'common.enabled',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.yes'],
            ['value' => '0', 'label_key' => 'common.no'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    ['name' => 'ga4_id', 'label_key' => 'common.ga4_id', 'type' => 'string', 'required' => false],
    ['name' => 'baidu_id', 'label_key' => 'common.baidu_id', 'type' => 'string', 'required' => false],
    ['name' => 'meta_pixel_id', 'label_key' => 'common.meta_pixel_id', 'type' => 'string', 'required' => false],
    ['name' => 'tiktok_pixel_id', 'label_key' => 'common.tiktok_pixel_id', 'type' => 'string', 'required' => false],
    ['name' => 'custom_head', 'label_key' => 'common.custom_head', 'type' => 'textarea', 'required' => false],
];
