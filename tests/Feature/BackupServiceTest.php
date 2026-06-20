<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use NiceShoply\Common\Models\Backup;
use NiceShoply\Common\Services\Ops\BackupService;
use Tests\TestCase;

/**
 * 系统备份服务集成测试。
 */
class BackupServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_perform_creates_completed_backup_zip(): void
    {
        Cache::forget(BackupService::PROGRESS_KEY);
        Cache::forget(BackupService::LOCK_KEY);

        $backup = Backup::query()->create([
            'type'         => Backup::TYPE_FULL,
            'status'       => Backup::STATUS_PENDING,
            'triggered_by' => 'manual',
        ]);

        BackupService::getInstance()->perform($backup->id);

        $backup->refresh();
        $this->assertSame(Backup::STATUS_COMPLETED, $backup->status);
        $this->assertNotEmpty($backup->file_path);
        $this->assertNotEmpty($backup->checksum);
        $this->assertFileExists(storage_path('app/'.$backup->file_path));

        $progress = BackupService::getInstance()->getProgress();
        $this->assertSame('success', $progress['status']);
    }

    public function test_queue_rejects_when_already_running(): void
    {
        Cache::put(BackupService::LOCK_KEY, now()->timestamp, 3600);
        Cache::put(BackupService::PROGRESS_KEY, ['status' => 'running'], 3600);

        $this->expectException(\Exception::class);
        BackupService::getInstance()->queue('manual');
    }
}
