<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use NiceShoply\Common\Middleware\RedirectMiddleware;
use NiceShoply\Common\Models\Redirect;
use NiceShoply\Common\Repositories\RedirectRepo;
use Tests\TestCase;

/**
 * URL 重定向中间件集成测试。
 */
class RedirectTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * 经 RedirectMiddleware 处理请求（测试环境未挂载 front 中间件组）。
     */
    private function throughMiddleware(string $path): \Symfony\Component\HttpFoundation\Response
    {
        $middleware = app(RedirectMiddleware::class);
        $request    = Request::create($path, 'GET');

        return $middleware->handle($request, fn () => response('', 404));
    }

    public function test_active_redirect_returns_301_and_increments_hits(): void
    {
        RedirectRepo::getInstance()->create([
            'source_path' => '/legacy-product-page',
            'target_path' => '/products',
            'status_code' => 301,
            'active'      => true,
        ]);

        $response = $this->throughMiddleware('/legacy-product-page');

        $this->assertSame(301, $response->getStatusCode());
        $this->assertStringContainsString('/products', $response->headers->get('Location'));

        $record = Redirect::query()->where('source_path', '/legacy-product-page')->first();
        $this->assertSame(1, $record->hits);
    }

    public function test_inactive_redirect_is_ignored(): void
    {
        Redirect::query()->create([
            'source_path' => '/disabled-redirect',
            'target_path' => '/products',
            'status_code' => 301,
            'active'      => false,
        ]);
        RedirectRepo::getInstance()->flushCache();

        $response = $this->throughMiddleware('/disabled-redirect');
        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_external_target_redirects_away(): void
    {
        RedirectRepo::getInstance()->create([
            'source_path' => '/go-external',
            'target_path' => 'https://example.com/landing',
            'status_code' => 302,
            'active'      => true,
        ]);

        $response = $this->throughMiddleware('/go-external');
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('https://example.com/landing', $response->headers->get('Location'));
    }
}
