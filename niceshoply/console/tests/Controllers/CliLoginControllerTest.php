<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use Illuminate\Support\Facades\URL;
use NiceShoply\Common\Models\Admin;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * CLI 一次性登录控制器测试。
 *
 * 覆盖：
 *  - 有效签名链接 → 免密登录并重定向后台
 *  - 篡改/无签名链接 → 403
 *  - 过期链接 → 403
 *  - 禁用账号 → 403
 */
class CliLoginControllerTest extends ConsoleTestCase
{
    private function makeAdmin(bool $active = true): Admin
    {
        $admin = new Admin;
        $admin->fill([
            'name'     => 'CLI 登录测试',
            'email'    => 'cli_'.uniqid().'@niceshoply.test',
            'password' => bcrypt('password'),
            'locale'   => 'zh-cn',
            'active'   => $active,
        ]);
        $admin->save();

        return $admin;
    }

    private function signedUrl(Admin $admin, int $minutes = 15): string
    {
        return URL::temporarySignedRoute(
            console_name().'.cli_login',
            now()->addMinutes($minutes),
            ['admin' => $admin->id]
        );
    }

    public function test_valid_signed_link_logs_in_admin(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->get($this->signedUrl($admin));

        $response->assertRedirect(console_route('home.index'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_tampered_signature_is_rejected(): void
    {
        $admin = $this->makeAdmin();

        $url = $this->signedUrl($admin).'tampered';

        $this->get($url)->assertStatus(403);
        $this->assertGuest('admin');
    }

    public function test_expired_link_is_rejected(): void
    {
        $admin = $this->makeAdmin();

        // 生成 1 分钟有效的链接，时间快进 2 分钟后失效
        $url = $this->signedUrl($admin, 1);
        $this->travel(2)->minutes();

        $this->get($url)->assertStatus(403);
        $this->assertGuest('admin');
    }

    public function test_disabled_admin_cannot_login(): void
    {
        $admin = $this->makeAdmin(active: false);

        $this->get($this->signedUrl($admin))->assertStatus(403);
        $this->assertGuest('admin');
    }
}
