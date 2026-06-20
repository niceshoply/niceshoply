<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI;

use Exception;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use NiceShoply\Common\Middleware\ContentFilterHook;
use NiceShoply\Common\Middleware\EventActionHook;
use NiceShoply\RestAPI\Middleware\SetAPICurrency;
use NiceShoply\RestAPI\Middleware\SetAPILocale;

class RestAPIServiceProvider extends ServiceProvider
{
    private array $middlewares = [
        SetAPILocale::class,
        SetAPICurrency::class,
        EventActionHook::class,
        ContentFilterHook::class,
    ];

    /**
     * Boot front service provider.
     *
     * @return void
     * @throws Exception
     */
    public function boot(): void
    {
        if (! installed()) {
            return;
        }

        load_settings();
        $this->registerFrontApiRoutes();
        $this->registerConsoleApiRoutes();
    }

    /**
     * Register frontend api routes.
     *
     * @return void
     */
    protected function registerFrontApiRoutes(): void
    {
        $router = $this->app['router'];
        foreach ($this->middlewares as $middleware) {
            $router->pushMiddlewareToGroup('api', $middleware);
        }

        Route::prefix('api/v1')
            ->middleware('api')
            ->name('api.')
            ->group(function () {
                $this->loadRoutesFrom(realpath(__DIR__.'/../routes/front-api.php'));
            });
    }

    /**
     * Register admin api routes.
     *
     * @return void
     */
    private function registerConsoleApiRoutes(): void
    {
        $router = $this->app['router'];
        foreach ($this->middlewares as $middleware) {
            $router->pushMiddlewareToGroup('console_api', $middleware);
        }

        Route::prefix('api/v1/console')
            ->middleware('console_api')
            ->name('api.console.')
            ->group(function () {
                $this->loadRoutesFrom(realpath(__DIR__.'/../routes/console-api.php'));
            });
    }
}
