<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 财务对账 Controller 测试。
 */
class ReconciliationControllerTest extends ConsoleTestCase
{
    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['reconciliation_index']);
        $this->get($this->consoleUrl('reconciliation.index'))->assertStatus(200);
    }

    public function test_export_returns_download_response(): void
    {
        $this->loginAdmin(['reconciliation_export']);
        $response = $this->get($this->consoleUrl('reconciliation.export', [
            'start' => now()->subDays(7)->toDateString(),
            'end'   => now()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
    }
}
