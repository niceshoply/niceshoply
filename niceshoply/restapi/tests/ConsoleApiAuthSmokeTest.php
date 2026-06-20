<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * 后台 REST API（console-api，JWT）全路由鉴权冒烟测试。
 *
 * 自动遍历所有 api.console.* 受保护 GET 路由，断言「无 token → 401」，
 * 防止后续新增端点漏挂 jwt.auth 中间件造成越权访问。
 */
class ConsoleApiAuthSmokeTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 收集所有无路径参数、受 jwt.auth 保护的 console-api GET 路由 URI。
     *
     * @return array<int, string>
     */
    private function protectedConsoleGetUris(): array
    {
        $uris = [];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName() ?? '';
            if (! str_starts_with($name, 'api.console.')) {
                continue;
            }
            if (! in_array('GET', $route->methods(), true)) {
                continue;
            }
            // 跳过带路径参数的路由（需具体资源），只冒烟无参列表/详情类端点
            if (str_contains($route->uri(), '{')) {
                continue;
            }
            // 公开端点（introduction）不在鉴权范围
            if (in_array($name, ['api.console.base.index'], true)) {
                continue;
            }
            $middlewares = $route->gatherMiddleware();
            if (! in_array('jwt.auth', $middlewares, true)) {
                continue;
            }

            $uris[] = '/'.ltrim($route->uri(), '/');
        }

        return array_values(array_unique($uris));
    }

    public function test_protected_console_api_routes_reject_unauthenticated_requests(): void
    {
        $uris = $this->protectedConsoleGetUris();

        $this->assertNotEmpty($uris, '应至少存在一个受保护的 console-api GET 路由');

        foreach ($uris as $uri) {
            $response = $this->getJson($uri);

            $this->assertContains(
                $response->getStatusCode(),
                [401],
                "受保护端点 {$uri} 在无 token 时应返回 401，实际 {$response->getStatusCode()}"
            );
        }
    }

    public function test_console_api_login_endpoint_is_public(): void
    {
        // 登录端点应可达（凭据错误返回 401/422，而非因鉴权中间件直接拦截）
        $response = $this->postJson('/api/v1/console/login', [
            'email'    => 'nobody-'.uniqid().'@niceshoply.test',
            'password' => 'wrong-password',
        ]);

        $this->assertContains($response->getStatusCode(), [401, 422]);
    }
}
