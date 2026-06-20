<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Services\JwtTokenService;
use Tests\TestCase;

/**
 * REST API 鉴权测试（front-api，JWT）。
 *
 * 验证：
 *  - 受保护端点无 token → 401
 *  - 伪造 / 非法 token → 401
 *  - 有效 customer_api token → 放行（200）
 *  - 公开端点无需 token 即可访问
 */
class ApiAuthTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCustomer(): Customer
    {
        return Customer::query()->create([
            'email'             => 'api-'.uniqid().'@example.com',
            'password'          => bcrypt('secret-password'),
            'name'              => 'API Tester',
            'customer_group_id' => 0,
            'active'            => true,
        ]);
    }

    private function tokenFor(Customer $customer): string
    {
        return app(JwtTokenService::class)
            ->issueToken($customer, 'customer_api', 'phpunit')['access_token'];
    }

    public function test_protected_endpoint_requires_token(): void
    {
        $this->getJson('/api/v1/account/me')
            ->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_protected_endpoint_rejects_invalid_token(): void
    {
        $this->getJson('/api/v1/account/me', [
            'Authorization' => 'Bearer this.is.not.a.valid.jwt',
        ])->assertStatus(401);
    }

    public function test_valid_token_grants_access_to_protected_endpoint(): void
    {
        $customer = $this->makeCustomer();
        $token    = $this->tokenFor($customer);

        $response = $this->getJson('/api/v1/account/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString($customer->email, $response->getContent());
    }

    public function test_public_endpoint_does_not_require_token(): void
    {
        // /api/v1/settings 为公开端点，无 token 也应返回 200
        $this->getJson('/api/v1/settings')->assertStatus(200);
    }
}
