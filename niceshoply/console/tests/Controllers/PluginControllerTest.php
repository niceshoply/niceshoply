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
use NiceShoply\Plugin\PluginServiceProvider;
use ReflectionProperty;

/**
 * 后台插件管理 Controller 测试。
 *
 * 覆盖插件列表可访问性、权限校验、未登录拦截与启用/禁用接口的容错。
 *
 * 说明：PluginServiceProvider 使用静态 $booted 防止 Octane 下重复 boot，
 * 该静态在同一测试进程内会跨用例保留，导致后续用例新建的应用不再注册插件路由。
 * 因此在每个用例创建应用前，通过反射复位该标志，保证插件路由可用。
 */
class PluginControllerTest extends ConsoleTestCase
{
    protected function setUp(): void
    {
        $property = new ReflectionProperty(PluginServiceProvider::class, 'booted');
        $property->setAccessible(true);
        $property->setValue(null, false);

        parent::setUp();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('plugins.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('plugins.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['plugins_index']);
        $this->get($this->consoleUrl('plugins.index'))->assertStatus(200);
    }

    public function test_update_status_with_invalid_code_fails_gracefully(): void
    {
        $this->loginAdmin(['plugins_update_status']);

        $this->post($this->consoleUrl('plugins.update_status'), [
            'code'    => 'NonExistentPlugin_'.uniqid(),
            'enabled' => 1,
        ])->assertJson(['success' => false]);
    }
}
