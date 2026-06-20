<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Console\Commands;

use Illuminate\Console\Command;
use NiceShoply\Common\Services\Ops\BackupService;
use Throwable;

/**
 * 执行系统备份（供 cron 调度或手动 CLI 调用）。
 */
class BackupRun extends Command
{
    protected $signature = 'backup:run {--sync : 同步执行，不投递队列}';

    protected $description = '创建系统完整备份（数据库 + 关键文件）';

    public function handle(): int
    {
        $service = BackupService::getInstance();

        try {
            if ($this->option('sync')) {
                $backup = \NiceShoply\Common\Models\Backup::query()->create([
                    'type'         => \NiceShoply\Common\Models\Backup::TYPE_FULL,
                    'status'       => \NiceShoply\Common\Models\Backup::STATUS_PENDING,
                    'triggered_by' => 'schedule',
                ]);
                $service->perform($backup->id);
                $backup->refresh();
                if ($backup->status === \NiceShoply\Common\Models\Backup::STATUS_COMPLETED) {
                    $this->info('备份完成：'.$backup->file_path);

                    return self::SUCCESS;
                }

                $this->error($backup->error_message ?? '备份失败');

                return self::FAILURE;
            }

            $backup = $service->queue('schedule');
            $this->info('备份任务已投递，ID：'.$backup->id);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
