<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Tests\TestCase;

/**
 * 安装模块安全回归测试（对应上游 #313 预认证重装接管漏洞）。
 *
 * 测试环境视为「已安装」（存在 storage/installed），因此所有安装端点
 * 都应被 PreventReinstall 中间件拦截，禁止再次触发 setup。
 */
class InstallSecurityTest extends TestCase
{
    public function test_install_index_redirects_when_already_installed(): void
    {
        $this->assertTrue(installed(), '测试环境应处于已安装状态');

        $this->get('/install')->assertRedirect();
    }

    public function test_install_complete_is_blocked_when_installed(): void
    {
        // 模拟攻击者在已安装站点尝试重新执行安装
        $this->postJson('/install/complete', [
            'db_type'        => 'sqlite',
            'admin_email'    => 'attacker@evil.test',
            'admin_password' => 'password123',
        ])->assertStatus(403);
    }

    public function test_install_connected_is_blocked_when_installed(): void
    {
        $this->postJson('/install/connected', [
            'db_hostname' => '127.0.0.1',
        ])->assertStatus(403);
    }
}
