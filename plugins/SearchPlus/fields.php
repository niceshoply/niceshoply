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
        'name'      => 'driver',
        'label_key' => 'common.driver',
        'type'      => 'select',
        'options'   => [
            ['value' => 'database', 'label_key' => 'common.driver_db'],
            ['value' => 'meilisearch', 'label_key' => 'common.driver_meili'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    ['name' => 'limit', 'label_key' => 'common.limit', 'type' => 'string', 'default' => '20', 'rules' => 'nullable|integer|min:1|max:100'],
    ['name' => 'fallback_hot', 'label_key' => 'common.fallback_hot', 'type' => 'switch', 'default' => true],
    // Meilisearch 配置（driver=meilisearch 时使用）
    ['name' => 'meili_host', 'label_key' => 'common.meili_host', 'type' => 'string', 'required' => false, 'default' => 'http://127.0.0.1:7700'],
    ['name' => 'meili_key', 'label_key' => 'common.meili_key', 'type' => 'string', 'required' => false],
    ['name' => 'meili_index', 'label_key' => 'common.meili_index', 'type' => 'string', 'required' => false, 'default' => 'products'],
];
