<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 弃购挽回后台 Controller 测试。
 */
class AbandonedCartControllerTest extends ConsoleTestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('abandoned_carts.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('abandoned_carts.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['abandoned_carts_index']);
        $this->get($this->consoleUrl('abandoned_carts.index'))->assertStatus(200);
    }
}
