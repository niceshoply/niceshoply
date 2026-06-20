<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'       => 'Image Optimize/Watermark',
    'text'       => 'Watermark text',
    'position'   => 'Position',
    'pos_br'     => 'Bottom right',
    'pos_bl'     => 'Bottom left',
    'pos_tr'     => 'Top right',
    'pos_tl'     => 'Top left',
    'pos_center' => 'Center',
    'opacity'    => 'Opacity (0-100)',
    'font_size'  => 'Font size (px, needs TTF)',
    'font_path'  => 'TTF font absolute path (optional)',
    'color'      => 'Text color (hex)',
    'quality'    => 'Output quality (1-100)',

    'preview_title' => 'Watermark preview',
    'preview_desc'  => 'Upload an image to preview current watermark settings (original is not modified).',
    'preview_btn'   => 'Upload & preview',

    'process_title' => 'Batch process folder',
    'process_desc'  => 'Enter a relative folder under storage/app/public (e.g. products) to watermark and recompress its images (overwrites originals — back up first). Or run: php artisan image:watermark <dir>',
    'dir'           => 'Folder (relative to storage/app/public)',
    'process_btn'   => 'Start',
    'need_dir'      => 'Please enter a folder',
    'done'          => 'Done: processed :processed, failed :failed',
    'result'        => 'Result',
];
