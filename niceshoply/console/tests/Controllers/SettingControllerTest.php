<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 后台系统设置 Controller 测试。
 *
 * 覆盖设置页可访问性、权限校验与关键设置读写。
 */
class SettingControllerTest extends ConsoleTestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('settings.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('settings.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['settings_index']);
        $this->get($this->consoleUrl('settings.index'))->assertStatus(200);
    }

    public function test_update_persists_setting_value(): void
    {
        $this->loginAdmin(['settings_update']);

        $newName = 'NiceShoply 测试商城';

        $this->put($this->consoleUrl('settings.update'), [
            'tab'        => 'general',
            'store_name' => $newName,
        ])->assertRedirect();

        $this->assertDatabaseHas('settings', [
            'space' => 'system',
            'name'  => 'store_name',
            'value' => $newName,
        ]);
    }
}
