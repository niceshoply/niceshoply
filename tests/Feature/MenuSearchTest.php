<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use NiceShoply\Common\Models\Admin;
use NiceShoply\Console\Repositories\MenuSearchRepo;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * 后台菜单全局搜索测试（IMP-08）
 */
class MenuSearchTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 创建并登录一个授予指定权限的管理员。
     *
     * @param  array  $permissionCodes  需要授予的权限码（route 转下划线）
     * @return Admin
     */
    private function loginAdminWithPermissions(array $permissionCodes): Admin
    {
        Cache::flush();

        $admin = new Admin;
        $admin->fill([
            'name'     => '测试管理员',
            'email'    => 'menu_search_'.uniqid().'@niceshoply.test',
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
     * 空关键词应返回全部可见菜单项。
     */
    public function test_empty_keyword_returns_all_items(): void
    {
        $this->loginAdminWithPermissions(['products_index', 'orders_index']);

        $items = MenuSearchRepo::getInstance()->search('');

        $this->assertNotEmpty($items);
        $routes = array_column($items, 'route');
        // 仪表盘 skip_permission 恒在；授予的两个路由也应出现
        $this->assertContains('dashboard.index', $routes);
        $this->assertContains('products.index', $routes);
        $this->assertContains('orders.index', $routes);
    }

    /**
     * 未授权的菜单项不应出现。
     */
    public function test_unauthorized_items_excluded(): void
    {
        $this->loginAdminWithPermissions(['products_index']);

        $routes = array_column(MenuSearchRepo::getInstance()->search(''), 'route');
        $this->assertContains('products.index', $routes);
        $this->assertNotContains('orders.index', $routes);
    }

    /**
     * 关键词过滤应只返回匹配项。
     */
    public function test_keyword_filters_results(): void
    {
        $this->loginAdminWithPermissions(['orders_index', 'order_returns_index']);

        $orderTitle = __('console/menu.orders');
        $items      = MenuSearchRepo::getInstance()->search($orderTitle);

        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $haystack = mb_strtolower($item['title'].' '.($item['keywords'] ?? ''));
            $this->assertStringContainsString(mb_strtolower($orderTitle), $haystack);
        }
    }

    /**
     * 未登录管理员应返回空数组。
     */
    public function test_guest_returns_empty(): void
    {
        $items = MenuSearchRepo::getInstance()->search('order');
        $this->assertSame([], $items);
    }
}
