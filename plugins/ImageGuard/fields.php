<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    ['name' => 'text', 'label_key' => 'common.text', 'type' => 'string', 'default' => 'NiceShoply'],
    [
        'name'      => 'position',
        'label_key' => 'common.position',
        'type'      => 'select',
        'options'   => [
            ['value' => 'bottom-right', 'label_key' => 'common.pos_br'],
            ['value' => 'bottom-left', 'label_key' => 'common.pos_bl'],
            ['value' => 'top-right', 'label_key' => 'common.pos_tr'],
            ['value' => 'top-left', 'label_key' => 'common.pos_tl'],
            ['value' => 'center', 'label_key' => 'common.pos_center'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    // 透明度 0-100（100 最不透明）
    ['name' => 'opacity', 'label_key' => 'common.opacity', 'type' => 'string', 'default' => '50'],
    // 字号(px)，需配置 TTF 字体路径才能生效，否则使用内置位图字体
    ['name' => 'font_size', 'label_key' => 'common.font_size', 'type' => 'string', 'default' => '20'],
    ['name' => 'font_path', 'label_key' => 'common.font_path', 'type' => 'string', 'required' => false],
    // 文字颜色 hex，例如 #FFFFFF
    ['name' => 'color', 'label_key' => 'common.color', 'type' => 'string', 'default' => '#FFFFFF'],
    // 输出 JPEG/WebP 压缩质量 1-100
    ['name' => 'quality', 'label_key' => 'common.quality', 'type' => 'string', 'default' => '82'],
];
