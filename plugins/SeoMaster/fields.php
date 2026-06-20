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
    ['name' => 'organization_name', 'label_key' => 'common.organization_name', 'type' => 'string', 'required' => false],
    ['name' => 'default_description', 'label_key' => 'common.default_description', 'type' => 'textarea', 'required' => false],
    ['name' => 'default_keywords', 'label_key' => 'common.default_keywords', 'type' => 'string', 'required' => false],
    ['name' => 'og_image', 'label_key' => 'common.og_image', 'type' => 'string', 'required' => false],
    ['name' => 'twitter_site', 'label_key' => 'common.twitter_site', 'type' => 'string', 'required' => false],
    [
        'name'      => 'enable_sitemap',
        'label_key' => 'common.enable_sitemap',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.yes'],
            ['value' => '0', 'label_key' => 'common.no'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    ['name' => 'robots_txt', 'label_key' => 'common.robots_txt', 'type' => 'textarea', 'required' => false],
];
