<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use NiceShoply\Common\Services\Ops\HealthCheckService;
use Tests\TestCase;

/**
 * 健康自检服务测试。
 */
class HealthCheckTest extends TestCase
{
    public function test_run_all_returns_expected_check_keys(): void
    {
        $checks = HealthCheckService::getInstance()->runAll();

        $this->assertArrayHasKey('php', $checks);
        $this->assertArrayHasKey('extensions', $checks);
        $this->assertArrayHasKey('database', $checks);
        $this->assertArrayHasKey('storage', $checks);
        $this->assertArrayHasKey('disk', $checks);

        foreach ($checks as $check) {
            $this->assertArrayHasKey('ok', $check);
            $this->assertArrayHasKey('label', $check);
            $this->assertArrayHasKey('message', $check);
        }
    }

    public function test_php_check_passes_on_current_runtime(): void
    {
        $php = HealthCheckService::getInstance()->checkPhp();
        $this->assertTrue($php['ok']);
    }
}
