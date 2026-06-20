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
 * 后台订单管理 Controller 测试。
 *
 * 覆盖列表页可访问性、筛选参数与权限校验。订单创建依赖完整结账链路，
 * 此处聚焦读路径与鉴权（写路径由 OrderLifecycleTest / StateMachineServiceTest 覆盖）。
 */
class OrderControllerTest extends ConsoleTestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('orders.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('orders.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['orders_index']);
        $this->get($this->consoleUrl('orders.index'))->assertStatus(200);
    }

    public function test_index_accepts_status_filter(): void
    {
        $this->loginAdmin(['orders_index']);
        $this->get($this->consoleUrl('orders.index').'?status=10')->assertStatus(200);
    }
}
