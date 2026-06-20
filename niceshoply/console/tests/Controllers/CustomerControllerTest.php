<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\Customer;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 后台客户管理 Controller 测试。
 *
 * 覆盖列表/新建页可访问性、权限校验与删除路径。
 */
class CustomerControllerTest extends ConsoleTestCase
{
    private function makeCustomer(): Customer
    {
        return Customer::query()->create([
            'email'             => 'cust-'.uniqid().'@niceshoply.test',
            'password'          => bcrypt('secret-password'),
            'name'              => '测试客户',
            'customer_group_id' => 0,
            'active'            => true,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('customers.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('customers.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['customers_index']);
        $this->get($this->consoleUrl('customers.index'))->assertStatus(200);
    }

    public function test_create_page_accessible(): void
    {
        $this->loginAdmin(['customers_create']);
        $this->get($this->consoleUrl('customers.create'))->assertStatus(200);
    }

    public function test_destroy_removes_customer(): void
    {
        $this->loginAdmin(['customers_destroy']);

        $customer = $this->makeCustomer();

        $this->delete($this->consoleUrl('customers.destroy', $customer->id))
            ->assertRedirect($this->consoleUrl('customers.index'));

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }
}
