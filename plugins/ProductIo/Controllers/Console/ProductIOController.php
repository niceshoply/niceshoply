<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ProductIo\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\ProductIo\Services\ProductIOService;

class ProductIOController extends BaseController
{
    public function index(): mixed
    {
        return nice_view('ProductIo::console.index');
    }

    public function export()
    {
        return ProductIOService::getInstance()->export();
    }

    public function import(Request $request): mixed
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,txt',
            ]);

            $applyActive = $request->boolean('apply_active', false);
            $result = ProductIOService::getInstance()->import(
                $request->file('file')->getRealPath(),
                $applyActive
            );

            return json_success(
                __('ProductIo::common.import_done', ['updated' => $result['updated'], 'skipped' => $result['skipped']]),
                $result
            );
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
