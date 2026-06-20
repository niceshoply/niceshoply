<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'           => 'AI 翻译工具',
    'enabled'        => '启用 AI 翻译',
    'glossary'       => '术语表 / 品牌词',
    'yes'            => '是',
    'no'             => '否',

    'workbench'      => 'AI 翻译工作台',
    'source_lang'    => '源语言',
    'target_lang'    => '目标语言',
    'auto_detect'    => '自动检测',
    'mode_text'      => '文本翻译',
    'mode_lines'     => '批量文案(key = value)',
    'text_input'     => '原文',
    'lines_input'    => '每行一条：key = value',
    'translate'      => '翻译',
    'translating'    => '翻译中…',
    'result'         => '译文',
    'php_export'     => 'PHP 语言数组（可直接保存为 Lang 文件）',
    'copy'           => '复制',
    'warnings'       => '占位符校验告警（以下 key 的 :name/{count} 等占位符可能缺失或不一致）',
    'no_warnings'    => '占位符校验通过',
    'no_lines'       => '没有解析到有效的 key = value 行',
    'ai_unavailable' => 'AI 服务不可用，请先在「系统设置 - AI」中配置并启用至少一个模型',
    'tip'            => '提示：批量模式按「key = value」每行一条粘贴待译文案，翻译后自动生成可直接落地的 PHP 语言数组与占位符校验结果。',
];
