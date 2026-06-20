<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        // 在线升级：进入维护模式期间，放行后台升级进度查询接口，
        // 以便升级页面可持续轮询进度（路径含可变的后台前缀，用通配匹配）。
        $middleware->preventRequestsDuringMaintenance(except: [
            '*/system_update/progress',
            '*/system_update/log',
            '*/backups/progress',
        ]);

        $webMiddlewares = [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ];
        // 前台额外追加访问追踪中间件（记录访问与页面浏览事件，可由后台开关控制）
        $frontMiddlewares = array_merge($webMiddlewares, [
            \NiceShoply\Common\Middleware\RedirectMiddleware::class,
            \NiceShoply\Common\Middleware\VisitTrackingMiddleware::class,
        ]);
        $middleware->group('front', $frontMiddlewares);
        $middleware->group('console', $webMiddlewares);

        $apiMiddlewares = [
            \NiceShoply\Common\Middleware\RequestLogger::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ];
        $middleware->group('front_api', $apiMiddlewares);
        $middleware->group('console_api', $apiMiddlewares);

        // 注册 JWT 认证中间件别名
        $middleware->alias([
            'jwt.auth' => \NiceShoply\Common\Middleware\JwtAuthenticate::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if (\Illuminate\Support\Str::startsWith($request->route()->uri(), 'api')) {
                return front_route('home.index');
            }

            if (is_admin()) {
                return console_route('login.index');
            } else {
                return front_route('login.index');
            }
        });

        $middleware->validateCsrfTokens(except: [
            '*callback*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->dontReportDuplicates();

        // Sentry 异常监控
        $exceptions->reportable(function (Throwable $e) {
            if (app()->bound('sentry') && app()->environment('production', 'staging')) {
                app('sentry')->captureException($e);
            }

            // 统一通知：向已注册渠道推送严重异常（生产/预发环境）
            if (app()->environment('production', 'staging')) {
                \NiceShoply\Common\Services\Notification\NotificationEventSubscriber::notifyException($e);
            }
        });

        // 非 API 请求：保存真实异常供错误页展示（安装向导路径始终展示完整错误）
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return json_fail($e->getMessage());
            }

            app()->instance('_debug_exception', $e);

            return null;
        });

        // Handle 404 errors for frontend
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('console/*') || $request->is('admin/*')) {
                return null;
            }

            if (! $request->is('api/*')) {
                try {
                    return response()->view('errors.404', [], 404);
                } catch (Exception $exception) {
                    return null;
                }
            }

            return null;
        });
    })->create();
