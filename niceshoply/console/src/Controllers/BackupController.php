<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Backup;
use NiceShoply\Common\Repositories\BackupRepo;
use NiceShoply\Common\Services\Ops\BackupService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * 系统备份与恢复后台。
 */
class BackupController extends BaseController
{
    public function index(Request $request): mixed
    {
        $service = BackupService::getInstance();

        return nice_view('console::backups.index', [
            'backups'    => BackupRepo::getInstance()->builder($request->all())->paginate(20),
            'progress'   => $service->getProgress(),
            'is_running' => $service->isRunning(),
        ]);
    }

    public function store(Request $request): mixed
    {
        try {
            $admin  = current_admin();
            $backup = BackupService::getInstance()->queue('manual', $admin?->id ?? 0);

            return json_success(trans('console/backup.start_queued'), ['backup_id' => $backup->id]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function progress(): mixed
    {
        return json_success('', BackupService::getInstance()->getProgress());
    }

    public function download(int $id): BinaryFileResponse
    {
        $backup = BackupRepo::getInstance()->detail($id);
        abort_if(! $backup || $backup->status !== Backup::STATUS_COMPLETED || $backup->file_path === '', 404);

        $path = storage_path('app/'.$backup->file_path);
        abort_if(! is_file($path), 404);

        return response()->download($path, basename($backup->file_path));
    }

    public function restore(int $id): mixed
    {
        try {
            $backup = BackupRepo::getInstance()->detail($id);
            abort_if(! $backup, 404);

            if (BackupService::getInstance()->isRunning()) {
                return json_fail(trans('console/backup.already_running'));
            }

            BackupService::getInstance()->restore($backup);

            return json_success(trans('console/backup.restore_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
