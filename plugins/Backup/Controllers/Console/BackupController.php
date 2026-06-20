<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Backup\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Backup\Services\BackupService;

class BackupController extends BaseController
{
    public function index(): mixed
    {
        $backups = BackupService::getInstance()->list();

        return nice_view('Backup::console.index', compact('backups'));
    }

    public function create(): mixed
    {
        try {
            $file = BackupService::getInstance()->run();

            return json_success(__('Backup::common.created', ['file' => $file]));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function download(Request $request)
    {
        $name = (string) $request->query('name', '');
        $path = BackupService::getInstance()->pathFor($name);
        if (! $path) {
            abort(404);
        }

        return response()->download($path);
    }

    public function destroy(Request $request): mixed
    {
        $name = (string) $request->input('name', '');
        $ok = BackupService::getInstance()->delete($name);

        return $ok ? json_success(__('Backup::common.deleted')) : json_fail(__('Backup::common.delete_failed'));
    }
}
