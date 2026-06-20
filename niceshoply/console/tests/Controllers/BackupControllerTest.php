<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 备份后台 Controller 测试。
 */
class BackupControllerTest extends ConsoleTestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('backups.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('backups.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['backups_index']);
        $this->get($this->consoleUrl('backups.index'))->assertStatus(200);
    }

    public function test_health_index_with_permission(): void
    {
        $this->loginAdmin(['health_index']);
        $this->get($this->consoleUrl('health.index'))->assertStatus(200);
    }

    public function test_schedule_index_with_permission(): void
    {
        $this->loginAdmin(['schedule_index']);
        $this->get($this->consoleUrl('schedule.index'))->assertStatus(200);
    }
}
