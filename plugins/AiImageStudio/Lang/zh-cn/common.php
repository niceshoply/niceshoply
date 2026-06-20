<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'           => 'AI 商品图',
    'base_url'       => '图像服务地址(OpenAI 兼容)',
    'api_key'        => 'API Key',
    'model'          => '模型',
    'size'           => '尺寸',
    'size_square'    => '1024×1024（方形）',
    'size_portrait'  => '1024×1792（竖图）',
    'size_landscape' => '1792×1024（横图）',

    'empty_prompt'   => '请输入提示词',
    'no_credentials' => '请先配置图像服务地址与 API Key',
    'no_result'      => 'AI 未返回图片',
    'generated'      => '已生成 :count 张',
    'deleted'        => '已删除',

    // console
    'title'          => 'AI 商品图生成',
    'tip'            => '输入提示词，AI 生成商品主图/场景图/海报底图。生成结果保存到媒体库，可复制 URL 用于商品图。',
    'prompt'         => '提示词',
    'count'          => '数量',
    'generate'       => '生成',
    'gallery'        => '图库',
    'copy_url'       => '复制 URL',
    'del'            => '删除',
    'no_data'        => '暂无生成记录',
    'confirm_del'    => '确认删除该图片？',
];
