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
    [
        'name'      => 'provider',
        'label_key' => 'common.provider',
        'type'      => 'select',
        'options'   => [
            ['value' => 'meiqia', 'label_key' => 'common.provider_meiqia'],
            ['value' => 'tawk', 'label_key' => 'common.provider_tawk'],
            ['value' => 'crisp', 'label_key' => 'common.provider_crisp'],
            ['value' => 'custom', 'label_key' => 'common.provider_custom'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    // 美洽 entId / Tawk propertyId/widgetId(用 widget_id 存 "property/widget") / Crisp websiteId
    ['name' => 'widget_id', 'label_key' => 'common.widget_id', 'type' => 'string', 'required' => false],
    ['name' => 'custom_code', 'label_key' => 'common.custom_code', 'type' => 'textarea', 'required' => false],
];
