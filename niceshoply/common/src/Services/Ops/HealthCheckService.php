<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Ops;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use NiceShoply\Common\Repositories\ScheduleRunRepo;
use NiceShoply\Common\Services\BaseService;
use Throwable;

/**
 * 系统健康自检服务。
 */
class HealthCheckService extends BaseService
{
    /**
     * 运行全部检查项，返回结构化结果。
     *
     * @return array<string, array<string, mixed>>
     */
    public function runAll(): array
    {
        return [
            'php'         => $this->checkPhp(),
            'extensions'  => $this->checkExtensions(),
            'database'    => $this->checkDatabase(),
            'redis'       => $this->checkRedis(),
            'queue'       => $this->checkQueue(),
            'storage'     => $this->checkStorageWritable(),
            'cron'        => $this->checkCron(),
            'meilisearch' => $this->checkMeilisearch(),
            'disk'        => $this->checkDiskSpace(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkPhp(): array
    {
        $ok = version_compare(PHP_VERSION, '8.2.0', '>=');

        return [
            'ok'      => $ok,
            'label'   => 'PHP',
            'message' => 'PHP '.PHP_VERSION,
            'detail'  => $ok ? trans('console/health.php_ok') : trans('console/health.php_low'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkExtensions(): array
    {
        $required = ['pdo', 'mbstring', 'openssl', 'json', 'curl', 'fileinfo', 'zip'];
        $missing  = [];

        foreach ($required as $ext) {
            if (! extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        return [
            'ok'      => $missing === [],
            'label'   => trans('console/health.extensions'),
            'message' => $missing === [] ? trans('console/health.extensions_ok') : implode(', ', $missing),
            'detail'  => $missing === [] ? '' : trans('console/health.extensions_missing'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return [
                'ok'      => true,
                'label'   => trans('console/health.database'),
                'message' => config('database.default'),
                'detail'  => trans('console/health.database_ok'),
            ];
        } catch (Throwable $e) {
            return [
                'ok'      => false,
                'label'   => trans('console/health.database'),
                'message' => $e->getMessage(),
                'detail'  => trans('console/health.database_fail'),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function checkRedis(): array
    {
        if (config('cache.default') !== 'redis' && config('queue.default') !== 'redis') {
            return [
                'ok'      => true,
                'label'   => 'Redis',
                'message' => trans('console/health.redis_skipped'),
                'detail'  => '',
            ];
        }

        try {
            Redis::connection()->ping();

            return [
                'ok'      => true,
                'label'   => 'Redis',
                'message' => trans('console/health.redis_ok'),
                'detail'  => '',
            ];
        } catch (Throwable $e) {
            return [
                'ok'      => false,
                'label'   => 'Redis',
                'message' => $e->getMessage(),
                'detail'  => trans('console/health.redis_fail'),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function checkQueue(): array
    {
        $driver = config('queue.default');

        try {
            $size = Queue::size();

            return [
                'ok'      => true,
                'label'   => trans('console/health.queue'),
                'message' => $driver.' · '.trans('console/health.queue_pending', ['count' => $size]),
                'detail'  => trans('console/health.queue_ok'),
            ];
        } catch (Throwable $e) {
            return [
                'ok'      => false,
                'label'   => trans('console/health.queue'),
                'message' => $e->getMessage(),
                'detail'  => trans('console/health.queue_fail'),
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function checkStorageWritable(): array
    {
        $paths = [
            storage_path('app'),
            storage_path('logs'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            base_path('bootstrap/cache'),
        ];

        $failed = [];
        foreach ($paths as $path) {
            if (! is_writable($path)) {
                $failed[] = $path;
            }
        }

        return [
            'ok'      => $failed === [],
            'label'   => trans('console/health.storage'),
            'message' => $failed === [] ? trans('console/health.storage_ok') : count($failed).' '.trans('console/health.storage_fail'),
            'detail'  => implode("\n", $failed),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkCron(): array
    {
        $lastRun = Cache::get('niceshoply.schedule.last_run_at');
        if (! $lastRun) {
            $latest  = ScheduleRunRepo::getInstance()->latestAny();
            $lastRun = $latest?->ran_at?->toDateTimeString();
        }

        if (! $lastRun) {
            return [
                'ok'      => false,
                'label'   => trans('console/health.cron'),
                'message' => trans('console/health.cron_never'),
                'detail'  => trans('console/health.cron_hint'),
            ];
        }

        $minutes = now()->diffInMinutes($lastRun);
        $ok      = $minutes <= 120;

        return [
            'ok'      => $ok,
            'label'   => trans('console/health.cron'),
            'message' => trans('console/health.cron_last', ['time' => $lastRun]),
            'detail'  => $ok ? trans('console/health.cron_ok') : trans('console/health.cron_stale'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function checkMeilisearch(): array
    {
        $host = config('scout.meilisearch.host');
        if (empty($host)) {
            return [
                'ok'      => true,
                'label'   => 'Meilisearch',
                'message' => trans('console/health.meilisearch_skipped'),
                'detail'  => '',
            ];
        }

        try {
            $ch = curl_init(rtrim($host, '/').'/health');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
            ]);
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $ok = $code === 200 && str_contains((string) $body, 'available');

            return [
                'ok'      => $ok,
                'label'   => 'Meilisearch',
                'message' => $ok ? trans('console/health.meilisearch_ok') : trans('console/health.meilisearch_fail'),
                'detail'  => $host,
            ];
        } catch (Throwable $e) {
            return [
                'ok'      => false,
                'label'   => 'Meilisearch',
                'message' => $e->getMessage(),
                'detail'  => $host,
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function checkDiskSpace(): array
    {
        $path      = storage_path();
        $free      = @disk_free_space($path);
        $total     = @disk_total_space($path);
        $minFreeMb = (int) system_setting('health_min_free_mb', 500);

        if ($free === false || $total === false) {
            return [
                'ok'      => true,
                'label'   => trans('console/health.disk'),
                'message' => trans('console/health.disk_unknown'),
                'detail'  => '',
            ];
        }

        $freeMb = (int) round($free / 1024 / 1024);
        $ok     = $freeMb >= $minFreeMb;

        return [
            'ok'      => $ok,
            'label'   => trans('console/health.disk'),
            'message' => trans('console/health.disk_free', ['mb' => $freeMb]),
            'detail'  => $ok ? trans('console/health.disk_ok') : trans('console/health.disk_low', ['min' => $minFreeMb]),
        ];
    }

    /**
     * 汇总是否全部通过。
     *
     * @param  array<string, array<string, mixed>>  $checks
     */
    public function isHealthy(array $checks): bool
    {
        foreach ($checks as $check) {
            if (! ($check['ok'] ?? false)) {
                return false;
            }
        }

        return true;
    }
}
