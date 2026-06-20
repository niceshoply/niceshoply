<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'       => '图片优化/水印',
    'text'       => '水印文字',
    'position'   => '水印位置',
    'pos_br'     => '右下',
    'pos_bl'     => '左下',
    'pos_tr'     => '右上',
    'pos_tl'     => '左上',
    'pos_center' => '居中',
    'opacity'    => '不透明度(0-100)',
    'font_size'  => '字号(px，需 TTF 字体)',
    'font_path'  => 'TTF 字体绝对路径(可选)',
    'color'      => '文字颜色(hex)',
    'quality'    => '输出质量(1-100)',

    'preview_title' => '水印预览',
    'preview_desc'  => '上传一张图片，预览当前水印设置效果（不会修改原图）。',
    'preview_btn'   => '上传预览',

    'process_title' => '批量处理目录',
    'process_desc'  => '填写 storage/app/public 下的相对目录(如 products)，对其中图片批量加水印并按质量压缩（会覆盖原图，请先备份）。也可用命令：php artisan image:watermark 目录',
    'dir'           => '目录(相对 storage/app/public)',
    'process_btn'   => '开始处理',
    'need_dir'      => '请填写目录',
    'done'          => '处理完成：成功 :processed，失败 :failed',
    'result'        => '结果',
];
