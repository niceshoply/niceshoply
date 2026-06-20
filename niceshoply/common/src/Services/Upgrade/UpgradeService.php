<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services\Upgrade;

use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use NiceShoply\Common\Jobs\UpgradeJob;
use NiceShoply\Common\Repositories\SettingRepo;
use PhpZip\ZipFile;
use Throwable;

/**
 * 系统在线升级服务
 *
 * 负责对接官方升级服务器（marketplace.niceshoply.com），完成：
 *   1. 检查更新（比对当前版本与官方最新版本）
 *   2. 下载升级包（zip）并做完整性校验（大小 / SHA256）
 *   3. 进入维护模式，备份将被覆盖的文件
 *   4. 解压覆盖主程序文件，执行数据库迁移、重建缓存
 *   5. 热重载常驻运行时（Octane / 队列 worker），退出维护模式
 *   6. 失败时按备份自动回滚
 *
 * 整个执行流程由队列任务 {@see UpgradeJob} 在后台调用 {@see perform()}，
 * 实时进度写入缓存，后台页面通过轮询读取。
 */
class UpgradeService
{
    /** 升级进度缓存键 */
    public const PROGRESS_KEY = 'niceshoply.upgrade.progress';

    /** 升级并发锁缓存键 */
    public const LOCK_KEY = 'niceshoply.upgrade.lock';

    /** 进度缓存有效期（秒） */
    private const PROGRESS_TTL = 7200;

    /** 允许 manifest 指定的 post_commands 白名单，防止服务器下发任意命令 */
    private const ALLOWED_POST_COMMANDS = [
        'config:clear', 'route:clear', 'view:clear', 'event:clear',
        'cache:clear', 'optimize:clear', 'storage:link', 'queue:restart',
        'octane:reload', 'migrate',
    ];

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return new self;
    }

    /**
     * 在线升级功能是否启用。
     */
    public function isEnabled(): bool
    {
        return (bool) config('niceshoply.upgrade.enabled', true);
    }

    /**
     * 当前已安装版本号。
     */
    public function getCurrentVersion(): string
    {
        return (string) config('niceshoply.version', '0.0.0');
    }

    /**
     * 当前构建号。
     */
    public function getCurrentBuild(): string
    {
        return (string) config('niceshoply.build', '');
    }

    /**
     * 当前版本（community / pro 等）。
     */
    public function getCurrentEdition(): string
    {
        return (string) config('niceshoply.edition', 'community');
    }

    /**
     * 升级服务器 API 基础地址，如 https://marketplace.niceshoply.com/api/upgrade
     */
    private function apiBase(): string
    {
        return rtrim((string) config('niceshoply.api_url'), '/')
            .'/'.ltrim((string) config('niceshoply.upgrade.api_path', '/api/upgrade'), '/');
    }

    /**
     * 构建带授权头的 HTTP 客户端。
     */
    private function httpClient(int $timeout): PendingRequest
    {
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }

        $locale = function_exists('console_locale_code')
            ? console_locale_code()
            : (function_exists('locale_code') ? locale_code() : 'en');

        return Http::withOptions(['verify' => false])
            ->timeout($timeout)
            ->withHeaders([
                'domain-token' => (string) system_setting('domain_token'),
                'locale'       => $locale,
                'Accept'       => 'application/json',
            ]);
    }

    /**
     * 检查官方是否有新版本。
     *
     * @return array{success: bool, has_update?: bool, error?: string, data?: array}
     */
    public function check(): array
    {
        if (! $this->isEnabled()) {
            return ['success' => false, 'error' => trans('console/system_update.disabled')];
        }

        $domainToken = system_setting('domain_token');
        if (empty($domainToken)) {
            return ['success' => false, 'error' => trans('console/system_update.no_domain_token')];
        }

        try {
            $response = $this->httpClient((int) config('niceshoply.upgrade.check_timeout', 20))
                ->get($this->apiBase().'/check', [
                    'current_version' => $this->getCurrentVersion(),
                    'build'           => $this->getCurrentBuild(),
                    'edition'         => $this->getCurrentEdition(),
                ]);

            if ($response->status() !== 200) {
                $body = $response->json();

                return [
                    'success' => false,
                    'error'   => $body['message'] ?? trans('console/system_update.check_failed', ['code' => $response->status()]),
                ];
            }

            $json = $response->json();
            // 兼容 { success, data:{...} } 与直接返回 {...} 两种结构
            $data = $json['data'] ?? $json;

            $latest    = $data['latest_version'] ?? $data['version'] ?? null;
            $hasUpdate = isset($data['has_update'])
                ? (bool) $data['has_update']
                : ($latest && version_compare($latest, $this->getCurrentVersion(), '>'));

            return [
                'success'    => true,
                'has_update' => $hasUpdate,
                'data'       => $data,
            ];
        } catch (Throwable $e) {
            Log::warning('Upgrade check failed: '.$e->getMessage());

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 是否正在升级中（锁存在或进度处于排队/执行态）。
     */
    public function isRunning(): bool
    {
        $status = $this->getProgress()['status'] ?? 'idle';

        return Cache::has(self::LOCK_KEY) || in_array($status, ['queued', 'running'], true);
    }

    /**
     * 将升级任务投递到队列后台执行。
     *
     * @param  array  $release  check() 返回的 data 节点
     * @return bool 是否成功入队
     * @throws Exception 已有任务在执行时
     */
    public function queue(array $release): bool
    {
        if (! $this->isEnabled()) {
            throw new Exception(trans('console/system_update.disabled'));
        }
        if ($this->isRunning()) {
            throw new Exception(trans('console/system_update.already_running'));
        }
        if (! Cache::add(self::LOCK_KEY, now()->timestamp, 3600)) {
            throw new Exception(trans('console/system_update.already_running'));
        }

        $version = $release['latest_version'] ?? $release['version'] ?? '';

        $this->writeProgress([
            'status'       => 'queued',
            'step'         => 'queued',
            'percent'      => 0,
            'message'      => trans('console/system_update.step_queued'),
            'version'      => $version,
            'from_version' => $this->getCurrentVersion(),
            'error'        => null,
            'started_at'   => now()->toDateTimeString(),
            'updated_at'   => now()->toDateTimeString(),
            'logs'         => [],
        ]);

        UpgradeJob::dispatch($release);

        return true;
    }

    /**
     * 执行完整升级流程（由队列任务调用）。
     *
     * @param  array  $release
     */
    public function perform(array $release): void
    {
        $version = $release['latest_version'] ?? $release['version'] ?? 'unknown';
        $from    = $this->getCurrentVersion();

        $workDir    = storage_path('app/'.trim((string) config('niceshoply.upgrade.work_dir', 'upgrade'), '/'));
        $zipPath    = $workDir.'/packages/'.$this->safeName($version).'.zip';
        $extractDir = $workDir.'/extract/'.$this->safeName($version);
        $backupDir  = $workDir.'/backup/'.$this->safeName($version);

        $maintenanceEntered = false;

        try {
            $this->updateProgress('start', 2, trans('console/system_update.step_start'), 'running', [
                'version'      => $version,
                'from_version' => $from,
            ]);

            // 1. 下载升级包
            $this->updateProgress('download', 12, trans('console/system_update.step_download'));
            $this->downloadPackage($release, $zipPath);

            // 2. 完整性校验
            $this->updateProgress('verify', 25, trans('console/system_update.step_verify'));
            $this->verifyPackage($zipPath, $release);

            // 3. 解压到临时目录并读取清单
            $this->updateProgress('extract', 35, trans('console/system_update.step_extract'));
            $manifest = $this->extractPackage($zipPath, $extractDir);

            // 4. 环境与版本要求校验
            $this->checkRequirements($manifest);

            // 5. 进入维护模式（覆盖文件前）
            $this->updateProgress('maintenance', 45, trans('console/system_update.step_maintenance'));
            $this->enterMaintenance();
            $maintenanceEntered = true;

            // 6. 备份将被覆盖/删除的文件
            $this->updateProgress('backup', 55, trans('console/system_update.step_backup'));
            $this->backupFiles($manifest, $backupDir);

            // 7. 覆盖文件 + 删除废弃文件
            $this->updateProgress('apply', 68, trans('console/system_update.step_apply'));
            $this->applyFiles($manifest);

            // 8. 数据库迁移
            $this->updateProgress('migrate', 80, trans('console/system_update.step_migrate'));
            $this->runMigrations($manifest);

            // 9. 重建缓存
            $this->updateProgress('cache', 88, trans('console/system_update.step_cache'));
            $this->rebuildCaches($manifest);

            // 10. 退出维护模式
            $this->exitMaintenance();
            $maintenanceEntered = false;

            // 11. 热重载常驻运行时
            $this->updateProgress('reload', 95, trans('console/system_update.step_reload'));
            $this->reloadRuntime();

            // 记录升级历史
            SettingRepo::getInstance()->updateSystemValue('app_last_upgrade_version', $version);
            SettingRepo::getInstance()->updateSystemValue('app_last_upgrade_at', now()->toDateTimeString());

            $this->updateProgress('done', 100, trans('console/system_update.success_done', ['version' => $version]), 'success');

            $this->cleanup($zipPath, $extractDir);
        } catch (Throwable $e) {
            Log::error('Upgrade failed: '.$e->getMessage(), [
                'version' => $version,
                'from'    => $from,
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            // 尝试回滚已覆盖的文件
            try {
                if (File::isDirectory($backupDir)) {
                    $this->appendLog(trans('console/system_update.rolling_back'));
                    $this->rollback($backupDir);
                }
            } catch (Throwable $rollbackError) {
                Log::error('Upgrade rollback failed: '.$rollbackError->getMessage());
                $this->appendLog('Rollback error: '.$rollbackError->getMessage());
            }

            // 确保退出维护模式
            if ($maintenanceEntered) {
                try {
                    $this->exitMaintenance();
                } catch (Throwable $ignore) {
                }
            }

            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }

            $this->updateProgress('failed', 100, $e->getMessage(), 'failed', ['error' => $e->getMessage()]);
        } finally {
            $this->releaseLock();
        }
    }

    // ====================== 执行步骤 ======================

    /**
     * 下载升级包（流式写入磁盘，避免大包占用内存）。
     */
    private function downloadPackage(array $release, string $zipPath): void
    {
        File::ensureDirectoryExists(dirname($zipPath));
        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $url = $release['download_url'] ?? ($this->apiBase().'/download/'.($release['latest_version'] ?? $release['version'] ?? ''));
        if (! Str::startsWith($url, ['http://', 'https://'])) {
            $url = rtrim((string) config('niceshoply.api_url'), '/').'/'.ltrim($url, '/');
        }

        $response = $this->httpClient((int) config('niceshoply.upgrade.download_timeout', 1200))
            ->sink($zipPath)
            ->get($url);

        if (! $response->successful()) {
            throw new Exception(trans('console/system_update.download_failed', ['code' => $response->status()]));
        }
        if (! File::exists($zipPath) || File::size($zipPath) === 0) {
            throw new Exception(trans('console/system_update.download_empty'));
        }

        $this->appendLog(trans('console/system_update.log_downloaded', ['size' => $this->humanSize(File::size($zipPath))]));
    }

    /**
     * 校验升级包大小与 SHA256 摘要。
     */
    private function verifyPackage(string $zipPath, array $release): void
    {
        $expectedSize = $release['size'] ?? null;
        if ($expectedSize && (int) $expectedSize !== File::size($zipPath)) {
            throw new Exception(trans('console/system_update.size_mismatch'));
        }

        $checksum = $release['checksum'] ?? $release['sha256'] ?? null;
        if ($checksum) {
            $checksum = strtolower(preg_replace('/^sha256:/i', '', (string) $checksum));
            $actual   = hash_file('sha256', $zipPath);
            if (! hash_equals($checksum, $actual)) {
                throw new Exception(trans('console/system_update.checksum_failed'));
            }
            $this->appendLog(trans('console/system_update.log_checksum_ok'));
        }
    }

    /**
     * 解压升级包到临时目录并读取 upgrade-manifest.json。
     *
     * @return array 清单数据，附加 _root 键指向真实解压根目录
     */
    private function extractPackage(string $zipPath, string $extractDir): array
    {
        if (File::isDirectory($extractDir)) {
            File::deleteDirectory($extractDir);
        }
        File::ensureDirectoryExists($extractDir);

        $zip = new ZipFile;
        try {
            $zip->openFile($zipPath)->extractTo($extractDir);
        } catch (Throwable $e) {
            throw new Exception(trans('console/system_update.extract_failed').': '.$e->getMessage());
        } finally {
            $zip->close();
        }

        $root = $this->resolveRoot($extractDir);

        $manifestPath = $root.'/upgrade-manifest.json';
        $manifest     = [];
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true) ?: [];
        }
        $manifest['_root'] = $root;

        $this->appendLog(trans('console/system_update.log_extracted'));

        return $manifest;
    }

    /**
     * 兼容升级包内可能存在的单层根目录（如 niceshoply-1.7.0/）。
     */
    private function resolveRoot(string $extractDir): string
    {
        if (File::exists($extractDir.'/upgrade-manifest.json')) {
            return $extractDir;
        }

        $dirs  = File::directories($extractDir);
        $files = File::files($extractDir, true);

        if (count($dirs) === 1 && count($files) === 0) {
            return $dirs[0];
        }

        return $extractDir;
    }

    /**
     * 校验升级包对运行环境的要求（PHP 版本、最低当前版本）。
     */
    private function checkRequirements(array $manifest): void
    {
        $requirements = $manifest['requirements'] ?? [];

        $php = $requirements['php'] ?? null;
        if ($php && ! $this->versionSatisfies(PHP_VERSION, $php)) {
            throw new Exception(trans('console/system_update.php_required', ['require' => $php, 'current' => PHP_VERSION]));
        }

        $minVersion = $manifest['min_version'] ?? null;
        if ($minVersion && version_compare($this->getCurrentVersion(), $minVersion, '<')) {
            throw new Exception(trans('console/system_update.min_version_required', ['min' => $minVersion]));
        }
    }

    /**
     * 备份将被覆盖或删除的文件，供失败回滚使用。
     */
    private function backupFiles(array $manifest, string $backupDir): void
    {
        $root = $manifest['_root'];
        if (File::isDirectory($backupDir)) {
            File::deleteDirectory($backupDir);
        }
        File::ensureDirectoryExists($backupDir);

        $backedUp = [];

        // 备份将被覆盖的现有文件
        foreach (File::allFiles($root) as $file) {
            $relative = $this->normalizeRelative($file->getRelativePathname());
            if ($relative === 'upgrade-manifest.json' || $this->isProtected($relative)) {
                continue;
            }

            $target = base_path($relative);
            if (File::exists($target)) {
                $dest = $backupDir.'/'.$relative;
                File::ensureDirectoryExists(dirname($dest));
                File::copy($target, $dest);
                $backedUp[] = $relative;
            }
        }

        // 备份将被删除的文件
        foreach ((array) ($manifest['delete'] ?? []) as $relative) {
            $relative = $this->normalizeRelative($relative);
            if ($this->isProtected($relative)) {
                continue;
            }
            $target = base_path($relative);
            if (File::exists($target) && File::isFile($target)) {
                $dest = $backupDir.'/'.$relative;
                File::ensureDirectoryExists(dirname($dest));
                File::copy($target, $dest);
                $backedUp[] = $relative;
            }
        }

        File::put($backupDir.'/.backup-files.json', json_encode(array_values(array_unique($backedUp)), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->appendLog(trans('console/system_update.log_backed_up', ['count' => count($backedUp)]));
    }

    /**
     * 覆盖主程序文件并删除清单中标记的废弃文件。
     */
    private function applyFiles(array $manifest): void
    {
        $root  = $manifest['_root'];
        $count = 0;

        foreach (File::allFiles($root) as $file) {
            $relative = $this->normalizeRelative($file->getRelativePathname());
            if ($relative === 'upgrade-manifest.json' || $this->isProtected($relative)) {
                continue;
            }

            $target = base_path($relative);
            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getRealPath(), $target);
            $count++;
        }

        // 删除废弃文件
        foreach ((array) ($manifest['delete'] ?? []) as $relative) {
            $relative = $this->normalizeRelative($relative);
            if ($this->isProtected($relative)) {
                continue;
            }
            $target = base_path($relative);
            if (File::exists($target) && File::isFile($target)) {
                File::delete($target);
            }
        }

        $this->appendLog(trans('console/system_update.log_applied', ['count' => $count]));
    }

    /**
     * 执行数据库迁移（manifest.migrate 为 false 时跳过）。
     */
    private function runMigrations(array $manifest): void
    {
        if (($manifest['migrate'] ?? true) === false) {
            return;
        }

        Artisan::call('migrate', ['--force' => true]);
        $output = trim(Artisan::output());
        if ($output !== '') {
            $this->appendLog($output);
        }
    }

    /**
     * 清理并重建缓存，执行清单内允许的善后命令。
     */
    private function rebuildCaches(array $manifest): void
    {
        foreach (['config:clear', 'route:clear', 'view:clear', 'event:clear'] as $cmd) {
            try {
                Artisan::call($cmd);
            } catch (Throwable $e) {
                Log::warning("Upgrade cache command failed [{$cmd}]: ".$e->getMessage());
            }
        }

        foreach ((array) ($manifest['post_commands'] ?? []) as $cmd) {
            $name = trim((string) (is_array($cmd) ? ($cmd['command'] ?? '') : $cmd));
            if ($name === '' || ! in_array($name, self::ALLOWED_POST_COMMANDS, true)) {
                continue;
            }
            try {
                Artisan::call($name, $name === 'migrate' ? ['--force' => true] : []);
            } catch (Throwable $e) {
                Log::warning("Upgrade post command failed [{$name}]: ".$e->getMessage());
            }
        }
    }

    /**
     * 热重载常驻运行时：队列 worker 与 Octane web worker。
     */
    private function reloadRuntime(): void
    {
        if (! (bool) config('niceshoply.upgrade.reload_runtime', true)) {
            return;
        }

        try {
            Artisan::call('queue:restart');
        } catch (Throwable $e) {
            Log::warning('queue:restart failed during upgrade: '.$e->getMessage());
        }

        // 仅在安装了 Octane 时尝试热重载（未运行 Octane 会抛错，已忽略）
        if (class_exists(\Laravel\Octane\Octane::class)) {
            try {
                Artisan::call('octane:reload');
            } catch (Throwable $e) {
                Log::warning('octane:reload skipped: '.$e->getMessage());
            }
        }
    }

    /**
     * 从备份目录还原被覆盖的文件。
     */
    private function rollback(string $backupDir): void
    {
        foreach (File::allFiles($backupDir) as $file) {
            $relative = $this->normalizeRelative($file->getRelativePathname());
            if ($relative === '.backup-files.json') {
                continue;
            }
            $target = base_path($relative);
            File::ensureDirectoryExists(dirname($target));
            File::copy($file->getRealPath(), $target);
        }

        try {
            Artisan::call('config:clear');
            Artisan::call('view:clear');
        } catch (Throwable $e) {
        }
    }

    /**
     * 进入维护模式。
     */
    private function enterMaintenance(): void
    {
        try {
            Artisan::call('down', [
                '--secret' => (string) config('niceshoply.upgrade.maintenance_secret', 'niceshoply-upgrade'),
                '--retry'  => 60,
            ]);
        } catch (Throwable $e) {
            Log::warning('Enter maintenance failed: '.$e->getMessage());
        }
    }

    /**
     * 退出维护模式。
     */
    private function exitMaintenance(): void
    {
        try {
            Artisan::call('up');
        } catch (Throwable $e) {
            Log::warning('Exit maintenance failed: '.$e->getMessage());
        }
    }

    /**
     * 清理临时文件。
     */
    private function cleanup(string $zipPath, string $extractDir): void
    {
        try {
            if (File::exists($zipPath)) {
                File::delete($zipPath);
            }
            if (File::isDirectory($extractDir)) {
                File::deleteDirectory($extractDir);
            }
        } catch (Throwable $e) {
        }
    }

    // ====================== 进度管理 ======================

    /**
     * 读取当前升级进度。
     */
    public function getProgress(): array
    {
        return Cache::get(self::PROGRESS_KEY, [
            'status'       => 'idle',
            'step'         => 'idle',
            'percent'      => 0,
            'message'      => '',
            'version'      => '',
            'from_version' => $this->getCurrentVersion(),
            'error'        => null,
            'started_at'   => null,
            'updated_at'   => null,
            'logs'         => [],
        ]);
    }

    /**
     * 重置进度为初始态。
     */
    public function resetProgress(): void
    {
        Cache::forget(self::PROGRESS_KEY);
    }

    /**
     * 释放升级锁。
     */
    public function releaseLock(): void
    {
        Cache::forget(self::LOCK_KEY);
    }

    /**
     * 标记升级失败（供队列任务超时 / 未捕获异常时补写）。
     */
    public function markFailed(string $message): void
    {
        $progress = $this->getProgress();

        // perform() 已自行标记失败时，仅释放锁，避免覆盖回滚日志
        if (($progress['status'] ?? '') !== 'failed') {
            $progress['status']     = 'failed';
            $progress['percent']    = 100;
            $progress['message']    = $message;
            $progress['error']      = $message;
            $progress['updated_at'] = now()->toDateTimeString();
            $progress['logs']       = $progress['logs'] ?? [];
            $progress['logs'][]     = ['time' => now()->toDateTimeString(), 'message' => $message];
            $this->writeProgress($progress);
        }

        $this->releaseLock();
    }

    /**
     * 整体写入进度。
     */
    private function writeProgress(array $progress): void
    {
        Cache::put(self::PROGRESS_KEY, $progress, self::PROGRESS_TTL);
    }

    /**
     * 更新进度的步骤、百分比与消息。
     */
    private function updateProgress(string $step, int $percent, string $message, string $status = 'running', array $extra = []): void
    {
        $progress = $this->getProgress();

        $progress['status']     = $status;
        $progress['step']       = $step;
        $progress['percent']    = $percent;
        $progress['message']    = $message;
        $progress['updated_at'] = now()->toDateTimeString();
        $progress               = array_merge($progress, $extra);

        $progress['logs']   = $progress['logs'] ?? [];
        $progress['logs'][] = ['time' => now()->toDateTimeString(), 'message' => $message];
        $progress['logs']   = array_slice($progress['logs'], -100);

        $this->writeProgress($progress);

        Log::info("Upgrade [{$step}] {$percent}% - {$message}");
    }

    /**
     * 追加一条日志（不改变步骤/百分比）。
     */
    private function appendLog(string $message): void
    {
        $progress               = $this->getProgress();
        $progress['logs']       = $progress['logs'] ?? [];
        $progress['logs'][]     = ['time' => now()->toDateTimeString(), 'message' => $message];
        $progress['logs']       = array_slice($progress['logs'], -100);
        $progress['updated_at'] = now()->toDateTimeString();

        $this->writeProgress($progress);
    }

    // ====================== 工具方法 ======================

    /**
     * 判断相对路径是否属于受保护范围。
     */
    private function isProtected(string $relative): bool
    {
        $relative = $this->normalizeRelative($relative);

        foreach ((array) config('niceshoply.upgrade.protected_paths', []) as $protected) {
            $protected = trim(str_replace('\\', '/', (string) $protected), '/');
            if ($protected === '') {
                continue;
            }
            if ($relative === $protected || Str::startsWith($relative, $protected.'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * 归一化相对路径（统一使用 / 分隔、去除首尾斜杠与 ../）。
     */
    private function normalizeRelative(string $relative): string
    {
        $relative = str_replace('\\', '/', $relative);
        $relative = ltrim($relative, '/');
        // 安全：剔除任何路径穿越片段
        $parts = [];
        foreach (explode('/', $relative) as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                continue;
            }
            $parts[] = $part;
        }

        return implode('/', $parts);
    }

    /**
     * 简单的版本约束判断，支持 >=, >, <=, <, = 前缀。
     */
    private function versionSatisfies(string $current, string $constraint): bool
    {
        $constraint = trim($constraint);
        if (preg_match('/^(>=|<=|>|<|=)?\s*(.+)$/', $constraint, $m)) {
            $operator = $m[1] ?: '>=';
            $target   = trim($m[2]);

            return version_compare($current, $target, $operator);
        }

        return version_compare($current, $constraint, '>=');
    }

    /**
     * 文件名安全化，避免版本号中的非法字符用于路径。
     */
    private function safeName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?: 'package';
    }

    /**
     * 人类可读的文件大小。
     */
    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i     = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
