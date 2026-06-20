<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Tests\Requests;

use NiceShoply\Console\Requests\BrandRequest;
use Tests\TestCase;

/**
 * PatchRequestTrait 部分更新校验测试。
 *
 * 以真实接入该 Trait 的 BrandRequest 为载体，验证：
 *  - PATCH 请求只校验「请求中出现」的字段（部分更新）；
 *  - PUT/POST 请求仍校验完整规则（整体替换，行为不变）。
 */
class PatchRequestTraitTest extends TestCase
{
    private function makeRequest(string $method, array $payload): BrandRequest
    {
        $request = BrandRequest::create('/console/brands/1', $method, $payload);

        // FormRequest 需绑定容器以解析校验工厂等依赖
        $request->setContainer($this->app)->setRedirector($this->app['redirect']);

        return $request;
    }

    public function test_patch_only_validates_present_fields(): void
    {
        // PATCH 仅提交 name 字段，规则应只剩 name
        $request = $this->makeRequest('PATCH', ['name' => 'New Brand']);

        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayNotHasKey('first', $rules);
        $this->assertArrayNotHasKey('logo', $rules);
        $this->assertTrue($request->isPatch());
    }

    public function test_patch_keeps_multiple_present_fields(): void
    {
        $request = $this->makeRequest('PATCH', ['name' => 'X', 'active' => true]);

        $rules = $request->rules();

        $this->assertEqualsCanonicalizing(['name', 'active'], array_keys($rules));
    }

    public function test_put_validates_full_rule_set(): void
    {
        // PUT 整体替换：完整 5 条规则全部保留
        $request = $this->makeRequest('PUT', ['name' => 'X']);

        $rules = $request->rules();

        $this->assertEqualsCanonicalizing(
            ['name', 'slug', 'first', 'logo', 'active'],
            array_keys($rules)
        );
        $this->assertFalse($request->isPatch());
    }

    public function test_post_validates_full_rule_set(): void
    {
        $request = $this->makeRequest('POST', ['name' => 'X']);

        $this->assertCount(5, $request->rules());
    }
}
