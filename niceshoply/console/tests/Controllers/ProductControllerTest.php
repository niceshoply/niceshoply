<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\Product;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 后台商品管理 Controller 测试。
 *
 * 覆盖列表/新建页可访问性、权限校验与上下架（active）写操作。
 */
class ProductControllerTest extends ConsoleTestCase
{
    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('products.index'))->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);
        $this->get($this->consoleUrl('products.index'))->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['products_index']);
        $this->get($this->consoleUrl('products.index'))->assertStatus(200);
    }

    public function test_create_page_accessible(): void
    {
        $this->loginAdmin(['products_create']);
        $this->get($this->consoleUrl('products.create'))->assertStatus(200);
    }

    public function test_toggle_active_updates_product(): void
    {
        $this->loginAdmin(['products_active']);

        $product = Product::query()->create([
            'active'   => 0,
            'brand_id' => 0,
            'price'    => 9.99,
            'quantity' => 5,
        ]);

        $this->put($this->consoleUrl('products.active', $product->id), ['status' => 1])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'active' => 1]);
    }
}
