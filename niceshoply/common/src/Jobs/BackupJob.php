<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Services\Ops\BackupService;
use Throwable;

/**
 * 系统备份队列任务。
 */
class BackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(private readonly int $backupId) {}

    public function handle(): void
    {
        BackupService::getInstance()->perform($this->backupId);
    }

    public function failed(?Throwable $e): void
    {
        Log::error('BackupJob failed: '.($e?->getMessage() ?? 'unknown'));
        BackupService::getInstance()->releaseLock();
    }
}
