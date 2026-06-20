<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Ops;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Jobs\BackupJob;
use NiceShoply\Common\Models\Backup;
use NiceShoply\Common\Services\BaseService;
use PhpZip\ZipFile;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * 系统备份与恢复服务（DB + 关键文件，队列 + 进度缓存）。
 */
class BackupService extends BaseService
{
    public const PROGRESS_KEY = 'niceshoply.backup.progress';

    public const LOCK_KEY = 'niceshoply.backup.lock';

    private const PROGRESS_TTL = 7200;

    public function isRunning(): bool
    {
        $status = $this->getProgress()['status'] ?? 'idle';

        return in_array($status, ['queued', 'running'], true);
    }

    /**
     * 投递备份任务到队列。
     */
    public function queue(string $triggeredBy = 'manual', int $adminId = 0): Backup
    {
        if ($this->isRunning()) {
            throw new Exception(trans('console/backup.already_running'));
        }
        if (! Cache::add(self::LOCK_KEY, now()->timestamp, 3600)) {
            throw new Exception(trans('console/backup.already_running'));
        }

        $backup = Backup::query()->create([
            'type'         => Backup::TYPE_FULL,
            'status'       => Backup::STATUS_PENDING,
            'triggered_by' => $triggeredBy,
            'admin_id'     => $adminId,
        ]);

        $this->writeProgress([
            'status'     => 'queued',
            'step'       => 'queued',
            'percent'    => 0,
            'message'    => trans('console/backup.step_queued'),
            'backup_id'  => $backup->id,
            'error'      => null,
            'started_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
            'logs'       => [],
        ]);

        BackupJob::dispatch($backup->id);

        return $backup;
    }

    /**
     * 执行备份（队列调用）。
     */
    public function perform(int $backupId): void
    {
        $backup = Backup::query()->find($backupId);
        if (! $backup) {
            $this->releaseLock();

            return;
        }

        $backup->status     = Backup::STATUS_RUNNING;
        $backup->started_at = Carbon::now();
        $backup->save();

        $this->updateProgress('start', 5, trans('console/backup.step_start'));

        $workDir = storage_path('app/backups/work/'.$backup->id);
        File::ensureDirectoryExists($workDir);

        $zipRelative = 'backups/backup-'.$backup->id.'-'.date('YmdHis').'.zip';
        $zipPath     = storage_path('app/'.$zipRelative);

        try {
            $this->updateProgress('database', 15, trans('console/backup.step_database'));

            $dbFile = $this->dumpDatabase($workDir);

            $this->updateProgress('files', 45, trans('console/backup.step_files'));
            $this->archiveImportantFiles($workDir);

            $this->updateProgress('compress', 70, trans('console/backup.step_compress'));
            File::ensureDirectoryExists(dirname($zipPath));

            $zip = new ZipFile;
            foreach (File::allFiles($workDir) as $file) {
                $zip->addFile($file->getPathname(), $file->getRelativePathname());
            }
            $zip->saveAsFile($zipPath);
            $zip->close();

            $checksum = hash_file('sha256', $zipPath);
            $size     = filesize($zipPath) ?: 0;

            $backup->status       = Backup::STATUS_COMPLETED;
            $backup->file_path    = $zipRelative;
            $backup->file_size    = $size;
            $backup->checksum     = $checksum;
            $backup->completed_at = Carbon::now();
            $backup->metadata     = [
                'driver'      => config('database.default'),
                'db_file'     => basename($dbFile),
                'app_version' => config('niceshoply.version', ''),
            ];
            $backup->save();

            if (function_exists('activity') && config('activitylog.enabled', false)) {
                activity('backup')
                    ->performedOn($backup)
                    ->withProperties([
                        'file_path' => $zipRelative,
                        'file_size' => $size,
                        'checksum'  => $checksum,
                    ])
                    ->log("Backup #{$backup->id} completed");
            }

            $this->updateProgress('done', 100, trans('console/backup.success'), 'success');
            File::deleteDirectory($workDir);
        } catch (Throwable $e) {
            Log::error('备份失败：'.$e->getMessage(), ['backup_id' => $backupId]);
            $backup->status        = Backup::STATUS_FAILED;
            $backup->error_message = $e->getMessage();
            $backup->completed_at  = Carbon::now();
            $backup->save();

            $this->updateProgress('failed', 0, $e->getMessage(), 'failed', ['error' => $e->getMessage()]);
            if (File::isDirectory($workDir)) {
                File::deleteDirectory($workDir);
            }
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * 从备份包恢复（数据库 + 文件，维护模式下执行）。
     */
    public function restore(Backup $backup): void
    {
        if ($backup->status !== Backup::STATUS_COMPLETED || $backup->file_path === '') {
            throw new Exception(trans('console/backup.restore_invalid'));
        }

        $zipPath = storage_path('app/'.$backup->file_path);
        if (! is_file($zipPath)) {
            throw new Exception(trans('console/backup.file_missing'));
        }

        $extractDir = storage_path('app/backups/restore/'.$backup->id);
        File::deleteDirectory($extractDir);
        File::ensureDirectoryExists($extractDir);

        Artisan::call('down', ['--retry' => 60]);

        try {
            $zip = new ZipFile;
            $zip->openFile($zipPath);
            $zip->extractTo($extractDir);
            $zip->close();

            $this->restoreDatabase($extractDir);
            $this->restoreFiles($extractDir);

            Artisan::call('up');

            if (function_exists('activity') && config('activitylog.enabled', false)) {
                activity('backup')
                    ->performedOn($backup)
                    ->withProperties(['file_path' => $backup->file_path])
                    ->log("Backup #{$backup->id} restored");
            }
        } catch (Throwable $e) {
            Artisan::call('up');
            throw $e;
        } finally {
            File::deleteDirectory($extractDir);
        }
    }

    public function getProgress(): array
    {
        return Cache::get(self::PROGRESS_KEY, [
            'status'     => 'idle',
            'step'       => 'idle',
            'percent'    => 0,
            'message'    => '',
            'backup_id'  => null,
            'error'      => null,
            'started_at' => null,
            'updated_at' => null,
            'logs'       => [],
        ]);
    }

    public function releaseLock(): void
    {
        Cache::forget(self::LOCK_KEY);
    }

    private function dumpDatabase(string $workDir): string
    {
        $connection = config('database.default');
        $driver     = config("database.connections.{$connection}.driver");

        if ($driver === 'sqlite') {
            $source = config("database.connections.{$connection}.database");
            $dest   = $workDir.'/database.sqlite';
            if (! is_file($source)) {
                throw new Exception('SQLite 数据库文件不存在');
            }
            copy($source, $dest);

            return $dest;
        }

        if ($driver === 'mysql') {
            $dest = $workDir.'/database.sql';
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port");
            $db   = config("database.connections.{$connection}.database");
            $user = config("database.connections.{$connection}.username");
            $pass = config("database.connections.{$connection}.password");

            $process = new Process([
                'mysqldump',
                '-h', (string) $host,
                '-P', (string) $port,
                '-u', (string) $user,
                $db,
            ]);
            $process->setTimeout(600);
            if ($pass) {
                $process->setEnv(['MYSQL_PWD' => (string) $pass]);
            }
            $process->run();

            if (! $process->isSuccessful()) {
                throw new Exception('mysqldump 失败：'.$process->getErrorOutput());
            }

            file_put_contents($dest, $process->getOutput());

            return $dest;
        }

        // 兜底：导出为 JSON 快照（测试/其他驱动）
        $tables  = DB::connection()->getSchemaBuilder()->getTableListing();
        $payload = [];
        foreach ($tables as $table) {
            $payload[$table] = DB::table($table)->get()->toArray();
        }
        $dest = $workDir.'/database.json';
        file_put_contents($dest, json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $dest;
    }

    private function archiveImportantFiles(string $workDir): void
    {
        $filesDir = $workDir.'/files';
        File::ensureDirectoryExists($filesDir);

        $paths = [
            storage_path('app/public') => 'storage/app/public',
        ];

        if (is_dir(public_path('uploads'))) {
            $paths[public_path('uploads')] = 'public/uploads';
        }

        foreach ($paths as $source => $relative) {
            if (! is_dir($source)) {
                continue;
            }
            $target = $filesDir.'/'.$relative;
            File::ensureDirectoryExists(dirname($target));
            File::copyDirectory($source, $target);
        }

        file_put_contents($workDir.'/manifest.json', json_encode([
            'created_at' => now()->toIso8601String(),
            'paths'      => array_values($paths),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function restoreDatabase(string $extractDir): void
    {
        $connection = config('database.default');
        $driver     = config("database.connections.{$connection}.driver");

        if (is_file($extractDir.'/database.sqlite')) {
            $target = config("database.connections.{$connection}.database");
            copy($extractDir.'/database.sqlite', $target);

            return;
        }

        if (is_file($extractDir.'/database.sql') && $driver === 'mysql') {
            $host = config("database.connections.{$connection}.host");
            $port = config("database.connections.{$connection}.port");
            $db   = config("database.connections.{$connection}.database");
            $user = config("database.connections.{$connection}.username");
            $pass = config("database.connections.{$connection}.password");

            $process = Process::fromShellCommandline(sprintf(
                'mysql -h %s -P %s -u %s %s < %s',
                escapeshellarg((string) $host),
                escapeshellarg((string) $port),
                escapeshellarg((string) $user),
                escapeshellarg((string) $db),
                escapeshellarg($extractDir.'/database.sql')
            ));
            if ($pass) {
                $process->setEnv(['MYSQL_PWD' => (string) $pass]);
            }
            $process->setTimeout(600);
            $process->run();
            if (! $process->isSuccessful()) {
                throw new Exception('mysql 恢复失败：'.$process->getErrorOutput());
            }
        }
    }

    private function restoreFiles(string $extractDir): void
    {
        $filesRoot = $extractDir.'/files';
        if (! is_dir($filesRoot)) {
            return;
        }

        foreach (File::directories($filesRoot) as $dir) {
            $name = basename($dir);
            if ($name === 'storage') {
                $from = $dir.'/app/public';
                $to   = storage_path('app/public');
                if (is_dir($from)) {
                    File::ensureDirectoryExists($to);
                    File::copyDirectory($from, $to);
                }
            }
            if ($name === 'public') {
                $from = $dir.'/uploads';
                $to   = public_path('uploads');
                if (is_dir($from)) {
                    File::ensureDirectoryExists($to);
                    File::copyDirectory($from, $to);
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function updateProgress(string $step, int $percent, string $message, string $status = 'running', array $extra = []): void
    {
        $progress = array_merge($this->getProgress(), [
            'status'     => $status,
            'step'       => $step,
            'percent'    => $percent,
            'message'    => $message,
            'updated_at' => now()->toDateTimeString(),
        ], $extra);

        $logs             = $progress['logs'] ?? [];
        $logs[]           = ['time' => now()->toDateTimeString(), 'step' => $step, 'message' => $message];
        $progress['logs'] = array_slice($logs, -50);

        $this->writeProgress($progress);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function writeProgress(array $data): void
    {
        Cache::put(self::PROGRESS_KEY, $data, self::PROGRESS_TTL);
    }
}
