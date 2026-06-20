<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\MemberLevel;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 会员等级后台 Controller 测试。
 */
class MemberLevelControllerTest extends ConsoleTestCase
{
    private function payload(): array
    {
        return [
            'name'             => 'Silver-'.uniqid(),
            'label'            => '银卡会员',
            'threshold_type'   => 'amount',
            'threshold_value'  => 1000,
            'discount_percent' => 5,
            'priority'         => 1,
            'active'           => 1,
        ];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('member_levels.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('member_levels.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['member_levels_index']);
        $this->get($this->consoleUrl('member_levels.index'))->assertStatus(200);
    }

    public function test_store_and_destroy(): void
    {
        $this->loginAdmin(['member_levels_store']);

        $payload = $this->payload();
        $this->post($this->consoleUrl('member_levels.store'), $payload)
            ->assertRedirect($this->consoleUrl('member_levels.index'));

        $level = MemberLevel::query()->where('name', $payload['name'])->first();
        $this->assertNotNull($level);

        $this->loginAdmin(['member_levels_destroy']);
        $this->delete($this->consoleUrl('member_levels.destroy', $level->id))
            ->assertRedirect();
    }
}
