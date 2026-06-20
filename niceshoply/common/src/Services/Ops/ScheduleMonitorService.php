<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Services\Ops;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use NiceShoply\Common\Models\ScheduleRun;
use NiceShoply\Common\Repositories\ScheduleRunRepo;
use NiceShoply\Common\Services\BaseService;
use Throwable;

/**
 * 计划任务监控：记录执行历史、提供可视化列表与手动触发。
 */
class ScheduleMonitorService extends BaseService
{
    /** @var array<string, array{expression: string, description: string}> */
    private const TRACKED_COMMANDS = [
        'horizon:snapshot'        => ['expression' => '*/5 * * * *', 'description' => 'Horizon 指标快照'],
        'order:complete'          => ['expression' => '0 2 * * *', 'description' => '自动完成已发货订单'],
        'warehouse:stock-warning' => ['expression' => '0 * * * *', 'description' => '仓库低库存预警'],
        'visit:aggregate'         => ['expression' => '0 1 * * *', 'description' => '访问统计聚合'],
        'geoip:update'            => ['expression' => '30 3 * * 3', 'description' => 'GeoLite2 更新'],
        'currency:update'         => ['expression' => '0 4 * * *', 'description' => '汇率更新'],
        'abandoned-cart:scan'     => ['expression' => '0 * * * *', 'description' => '弃购扫描召回'],
        'seo:generate-sitemap'    => ['expression' => '0 5 * * *', 'description' => '生成 Sitemap'],
        'backup:run'              => ['expression' => '0 3 * * *', 'description' => '系统数据备份'],
    ];

    /**
     * 注册 Laravel Schedule 事件监听。
     */
    public static function registerEventListeners(): void
    {
        $repo = ScheduleRunRepo::getInstance();

        Event::listen(ScheduledTaskStarting::class, function (ScheduledTaskStarting $event) {
            Cache::put('niceshoply.schedule.last_run_at', now()->toDateTimeString(), 86400);
        });

        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) use ($repo) {
            $command = self::extractCommandName($event->task);
            if ($command === '') {
                return;
            }

            $repo->record([
                'command'     => $command,
                'expression'  => self::TRACKED_COMMANDS[$command]['expression'] ?? '',
                'status'      => ScheduleRun::STATUS_SUCCESS,
                'duration_ms' => 0,
                'output'      => '',
                'ran_at'      => Carbon::now(),
            ]);
        });

        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event) use ($repo) {
            $command = self::extractCommandName($event->task);
            if ($command === '') {
                return;
            }

            $repo->record([
                'command'       => $command,
                'expression'    => self::TRACKED_COMMANDS[$command]['expression'] ?? '',
                'status'        => ScheduleRun::STATUS_FAILED,
                'duration_ms'   => 0,
                'output'        => '',
                'error_message' => $event->exception->getMessage(),
                'ran_at'        => Carbon::now(),
            ]);
        });
    }

    /**
     * 获取带上次执行结果的任务列表。
     *
     * @return array<int, array<string, mixed>>
     */
    public function listTasks(): array
    {
        $repo = ScheduleRunRepo::getInstance();
        $list = [];

        foreach (self::TRACKED_COMMANDS as $command => $meta) {
            $last   = $repo->latestByCommand($command);
            $list[] = [
                'command'     => $command,
                'expression'  => $meta['expression'],
                'description' => $meta['description'],
                'last_run'    => $last,
            ];
        }

        return $list;
    }

    /**
     * 手动触发命令（记录为 manual）。
     *
     * @return array{success: bool, output: string, error: string}
     */
    public function runManually(string $command): array
    {
        if (! array_key_exists($command, self::TRACKED_COMMANDS)) {
            return ['success' => false, 'output' => '', 'error' => trans('console/schedule.command_not_allowed')];
        }

        $started = microtime(true);
        $output  = '';
        $error   = '';

        try {
            Artisan::call($command);
            $output  = Artisan::output();
            $status  = ScheduleRun::STATUS_MANUAL;
            $success = true;
        } catch (Throwable $e) {
            $error   = $e->getMessage();
            $status  = ScheduleRun::STATUS_FAILED;
            $success = false;
        }

        $duration = (int) round((microtime(true) - $started) * 1000);

        ScheduleRunRepo::getInstance()->record([
            'command'       => $command,
            'expression'    => self::TRACKED_COMMANDS[$command]['expression'],
            'status'        => $status,
            'duration_ms'   => $duration,
            'output'        => mb_substr(trim($output), 0, 65000),
            'error_message' => $error,
            'ran_at'        => Carbon::now(),
        ]);

        return ['success' => $success, 'output' => trim($output), 'error' => $error];
    }

    /**
     * 从 Schedule Event 任务对象提取 artisan 命令名。
     */
    private static function extractCommandName(mixed $task): string
    {
        $raw = '';
        if (is_object($task)) {
            if (property_exists($task, 'command')) {
                $raw = (string) $task->command;
            } elseif (method_exists($task, 'getSummaryForDisplay')) {
                $raw = (string) $task->getSummaryForDisplay();
            }
        }

        if ($raw === '') {
            return '';
        }

        // 匹配 artisan 命令名，如 order:complete、backup:run
        if (preg_match("/artisan['\s]+([a-z0-9:\-]+)/i", $raw, $m)) {
            return $m[1];
        }

        if (preg_match('/\b([a-z][a-z0-9:\-]+)\b/i', $raw, $m)) {
            $name = $m[1];
            if (array_key_exists($name, self::TRACKED_COMMANDS)) {
                return $name;
            }
        }

        return '';
    }
}
