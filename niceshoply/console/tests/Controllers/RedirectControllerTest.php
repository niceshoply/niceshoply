<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\Redirect;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * URL 重定向后台 Controller 测试。
 */
class RedirectControllerTest extends ConsoleTestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('redirects.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('redirects.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['redirects_index']);
        $this->get($this->consoleUrl('redirects.index'))->assertStatus(200);
    }

    public function test_store_and_destroy(): void
    {
        $this->loginAdmin(['redirects_store']);

        $payload = [
            'source_path' => '/test-old-'.uniqid(),
            'target_path' => '/products',
            'status_code' => 301,
            'active'      => 1,
        ];

        $this->post($this->consoleUrl('redirects.store'), $payload)
            ->assertRedirect($this->consoleUrl('redirects.index'));

        $redirect = Redirect::query()->where('source_path', $payload['source_path'])->first();
        $this->assertNotNull($redirect);

        $this->loginAdmin(['redirects_destroy']);
        $this->delete($this->consoleUrl('redirects.destroy', $redirect->id))
            ->assertRedirect();
    }
}
