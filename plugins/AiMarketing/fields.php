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
    // 默认模型（留空则使用系统设置中的默认模型）
    [
        'name'      => 'default_provider',
        'label_key' => 'common.default_provider',
        'type'      => 'select',
        'options'   => [
            ['value' => '', 'label_key' => 'common.provider_system'],
            ['value' => 'deepseek', 'label_key' => 'common.provider_deepseek'],
            ['value' => 'doubao', 'label_key' => 'common.provider_doubao'],
            ['value' => 'hunyuan', 'label_key' => 'common.provider_hunyuan'],
            ['value' => 'qianwen', 'label_key' => 'common.provider_qianwen'],
            ['value' => 'kimi', 'label_key' => 'common.provider_kimi'],
            ['value' => 'openai', 'label_key' => 'common.provider_openai'],
        ],
        'required'    => false,
        'emptyOption' => false,
    ],
    // 默认语气风格
    [
        'name'      => 'default_tone',
        'label_key' => 'common.default_tone',
        'type'      => 'select',
        'options'   => [
            ['value' => 'planting', 'label_key' => 'common.tone_planting'],
            ['value' => 'pro', 'label_key' => 'common.tone_pro'],
            ['value' => 'promo', 'label_key' => 'common.tone_promo'],
        ],
        'required'    => false,
        'emptyOption' => false,
    ],
];
