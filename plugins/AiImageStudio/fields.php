<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    // OpenAI 兼容图像服务基础地址，如 https://api.openai.com/v1
    ['name' => 'base_url', 'label_key' => 'common.base_url', 'type' => 'string', 'required' => true, 'default' => 'https://api.openai.com/v1'],
    ['name' => 'api_key', 'label_key' => 'common.api_key', 'type' => 'string', 'required' => true],
    ['name' => 'model', 'label_key' => 'common.model', 'type' => 'string', 'required' => true, 'default' => 'dall-e-3'],
    [
        'name'      => 'size',
        'label_key' => 'common.size',
        'type'      => 'select',
        'options'   => [
            ['value' => '1024x1024', 'label_key' => 'common.size_square'],
            ['value' => '1024x1792', 'label_key' => 'common.size_portrait'],
            ['value' => '1792x1024', 'label_key' => 'common.size_landscape'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
];
