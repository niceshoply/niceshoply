<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use NiceShoply\Common\Middleware\ContentFilterHook;
use NiceShoply\Common\Middleware\EventActionHook;
use NiceShoply\Common\Models\Admin;
use NiceShoply\Console\Console\Commands\ChangeRootPassword;
use NiceShoply\Console\Console\Commands\CliLogin;
use NiceShoply\Console\Middleware\AdminAuthenticate;
use NiceShoply\Console\Middleware\GlobalConsoleData;
use NiceShoply\Console\Middleware\SetConsoleLocale;

class ConsoleServiceProvider extends ServiceProvider
{
    /**
     * Boot console service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        if (! has_install_lock()) {
            return;
        }

        load_settings();
        $this->registerWebRoutes();
        $this->registerCommands();
        $this->loadTranslations();
        $this->loadViewTemplates();
        $this->loadViewComponents();
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->registerGuard();
        app('router')->aliasMiddleware('admin_auth', AdminAuthenticate::class);
    }

    /**
     * Register admin user guard.
     */
    private function registerGuard(): void
    {
        Config::set('auth.providers.admin', [
            'driver' => 'eloquent',
            'model'  => Admin::class,
        ]);

        Config::set('auth.guards.admin', [
            'driver'   => 'session',
            'provider' => 'admin',
        ]);

        // JWT API guard for admin
        Config::set('auth.guards.admin_api', [
            'driver'   => 'jwt',
            'provider' => 'admin',
        ]);
    }

    /**
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ChangeRootPassword::class,
                CliLogin::class,
            ]);
        }
    }

    /**
     * Register admin console routes.
     *
     * @return void
     */
    private function registerWebRoutes(): void
    {
        $router      = $this->app['router'];
        $middlewares = [EventActionHook::class, ContentFilterHook::class, GlobalConsoleData::class, SetConsoleLocale::class];
        foreach ($middlewares as $middleware) {
            $router->pushMiddlewareToGroup('console', $middleware);
        }

        $adminName = console_name();
        Route::prefix($adminName)
            ->middleware('console')
            ->name("$adminName.")
            ->group(function () {
                $this->loadRoutesFrom(realpath(__DIR__.'/../routes/web.php'));
            });
    }

    /**
     * Register console language
     * @return void
     */
    private function loadTranslations(): void
    {
        if (! is_dir(__DIR__.'/../lang')) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'console');
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/console'),
        ], 'lang');
    }

    /**
     * Load view components.
     *
     * @return void
     */
    private function loadViewComponents(): void
    {
        $this->loadViewComponentsAs('console', [
            'data-criteria'          => Components\Data\Criteria::class,
            'data-sorter'            => Components\Data\Sorter::class,
            'data-info'              => Components\Data\DataInfo::class,
            'layout-sidebar'         => Components\Layout\Sidebar::class,
            'chart-line'             => Components\Charts\Line::class,
            'chart-pie'              => Components\Charts\Pie::class,
            'form-codemirror'        => Components\Forms\Codemirror::class,
            'form-autocomplete-list' => Components\Forms\AutocompleteList::class,
        ]);
    }

    /**
     * Load templates
     *
     * @return void
     */
    private function loadViewTemplates(): void
    {
        $originViewPath = nice_path('console/resources/views');
        $customViewPath = resource_path('views/vendor/console');

        $this->publishes([
            $originViewPath => $customViewPath,
        ], 'views');

        $this->loadViewsFrom($originViewPath, 'console');
    }
}
