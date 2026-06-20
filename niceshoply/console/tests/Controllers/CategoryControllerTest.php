<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Controllers;

use NiceShoply\Common\Models\Category;
use NiceShoply\Console\Tests\ConsoleTestCase;

/**
 * 后台分类管理 Controller 测试。
 *
 * 覆盖：未登录重定向、无权限 403、列表/新建页可访问、CRUD 写入、
 * 含子分类时禁止删除等核心业务路径。
 */
class CategoryControllerTest extends ConsoleTestCase
{
    /**
     * 构造一条合法的分类提交数据（满足 CategoryRequest 校验）。
     */
    private function categoryPayload(string $name): array
    {
        $locale = setting_locale_code();

        return [
            'slug'         => 'cat-'.uniqid(),
            'position'     => 0,
            'active'       => 1,
            'parent_id'    => 0,
            'translations' => [
                $locale => [
                    'locale' => $locale,
                    'name'   => $name,
                ],
            ],
        ];
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get($this->consoleUrl('categories.index'))
            ->assertRedirect();
    }

    public function test_index_forbidden_without_permission(): void
    {
        $this->loginAdmin([]);

        $this->get($this->consoleUrl('categories.index'))
            ->assertStatus(403);
    }

    public function test_index_accessible_with_permission(): void
    {
        $this->loginAdmin(['categories_index']);

        $this->get($this->consoleUrl('categories.index'))
            ->assertStatus(200);
    }

    public function test_create_page_accessible(): void
    {
        $this->loginAdmin(['categories_create']);

        $this->get($this->consoleUrl('categories.create'))
            ->assertStatus(200);
    }

    public function test_store_creates_category(): void
    {
        $this->loginAdmin(['categories_store']);

        $payload = $this->categoryPayload('集成测试分类');

        $this->post($this->consoleUrl('categories.store'), $payload)
            ->assertRedirect($this->consoleUrl('categories.index'));

        $this->assertDatabaseHas('categories', ['slug' => $payload['slug']]);
    }

    public function test_store_validation_fails_without_name(): void
    {
        $this->loginAdmin(['categories_store']);

        $payload                                  = $this->categoryPayload('占位');
        $locale                                   = setting_locale_code();
        $payload['translations'][$locale]['name'] = '';

        $this->post($this->consoleUrl('categories.store'), $payload)
            ->assertSessionHasErrors();
    }

    public function test_update_modifies_category(): void
    {
        $this->loginAdmin(['categories_update']);

        $category = Category::query()->create(['slug' => 'upd-'.uniqid(), 'position' => 0, 'active' => 1, 'parent_id' => 0]);

        $payload = $this->categoryPayload('更新后的名称');

        $this->put($this->consoleUrl('categories.update', $category->id), $payload)
            ->assertRedirect($this->consoleUrl('categories.index'));

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'slug' => $payload['slug']]);
    }

    public function test_destroy_removes_category(): void
    {
        $this->loginAdmin(['categories_destroy']);

        $category = Category::query()->create(['slug' => 'del-'.uniqid(), 'position' => 0, 'active' => 1, 'parent_id' => 0]);

        $this->delete($this->consoleUrl('categories.destroy', $category->id))
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_destroy_blocked_when_has_children(): void
    {
        $this->loginAdmin(['categories_destroy']);

        $parent = Category::query()->create(['slug' => 'p-'.uniqid(), 'position' => 0, 'active' => 1, 'parent_id' => 0]);
        Category::query()->create(['slug' => 'c-'.uniqid(), 'position' => 0, 'active' => 1, 'parent_id' => $parent->id]);

        $this->delete($this->consoleUrl('categories.destroy', $parent->id))
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }
}
