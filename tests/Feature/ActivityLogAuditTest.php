<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Redirect;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * S1-S4 新模型 ActivityLog 审计回归测试。
 */
class ActivityLogAuditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_redirect_update_writes_activity_log(): void
    {
        if (! config('activitylog.enabled', false)) {
            $this->markTestSkipped('ActivityLog 未启用');
        }

        $redirect = Redirect::query()->create([
            'source_path' => '/audit-test-'.uniqid(),
            'target_path' => '/products',
            'status_code' => 301,
            'active'      => true,
        ]);

        $redirect->update(['target_path' => '/categories']);

        $activity = Activity::query()
            ->where('subject_type', Redirect::class)
            ->where('subject_id', $redirect->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('admin', $activity->log_name);
    }
}
