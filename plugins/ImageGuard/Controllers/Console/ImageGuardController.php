<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ImageGuard\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\ImageGuard\Services\ImageGuardService;

class ImageGuardController extends BaseController
{
    public function index(): mixed
    {
        return nice_view('ImageGuard::console.index');
    }

    /**
     * 上传单图预览水印效果，返回 data URL。
     */
    public function preview(Request $request): mixed
    {
        try {
            $request->validate(['file' => 'required|file|image']);

            $tmp = $request->file('file')->getRealPath();
            $out = tempnam(sys_get_temp_dir(), 'wm_').'.jpg';
            ImageGuardService::getInstance()->process($tmp, $out);

            $data = 'data:image/jpeg;base64,'.base64_encode((string) file_get_contents($out));
            @unlink($out);

            return json_success('ok', ['preview' => $data]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 批量处理 storage/app/public 下的目录。
     */
    public function processDir(Request $request): mixed
    {
        try {
            $dir = trim((string) $request->input('dir', ''));
            if ($dir === '') {
                return json_fail(__('ImageGuard::common.need_dir'));
            }

            $result = ImageGuardService::getInstance()->processDirectory($dir);

            return json_success(
                __('ImageGuard::common.done', ['processed' => $result['processed'], 'failed' => $result['failed']]),
                $result
            );
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
