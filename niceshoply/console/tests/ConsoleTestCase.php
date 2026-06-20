<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use NiceShoply\Common\Models\Admin;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * 后台（console）Controller 测试基类。
 *
 * 提供「创建并登录指定权限的管理员」能力，统一后台 HTTP 集成测试的认证逻辑。
 * 后台权限模型：Spatie Permission，guard 为 admin，无 super-admin 旁路，
 * 因此测试需显式授予路由权限码（路由名去掉 console 前缀、点号转下划线）。
 */
abstract class ConsoleTestCase extends TestCase
{
    use DatabaseTransactions;

    /**
     * 创建并登录一个授予指定权限的管理员。
     *
     * @param  array  $permissionCodes  需要授予的权限码（如 categories_index）
     */
    protected function loginAdmin(array $permissionCodes = []): Admin
    {
        Cache::flush();

        $admin = new Admin;
        $admin->fill([
            'name'     => '测试管理员',
            'email'    => 'console_test_'.uniqid().'@niceshoply.test',
            'password' => bcrypt('password'),
            'locale'   => 'zh-cn',
            'active'   => true,
        ]);
        $admin->save();

        foreach ($permissionCodes as $code) {
            $permission = Permission::firstOrCreate(['name' => $code, 'guard_name' => 'admin']);
            $admin->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    /**
     * 生成后台路由 URL（自动加 console 名称前缀）。
     */
    protected function consoleUrl(string $name, mixed $parameters = []): string
    {
        return console_route($name, $parameters);
    }
}
