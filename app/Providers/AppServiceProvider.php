<?php

namespace App\Providers;

use App\Listeners\SendPaidOrderConfirmation;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use NiceShoply\Common\Events\OrderPaid;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // config:cache 后须读 config；APP_URL 为 https 时同步强制，避免安装向导覆盖 .env 后样式混合内容
        $appUrl = (string) config('app.url', '');
        if (config('app.force_https') || str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer', 'JWT')
                );
            });

        // 显式注册领域事件监听（事件位于 common 包，故不依赖 app/Listeners 自动发现）。
        Event::listen(OrderPaid::class, SendPaidOrderConfirmation::class);

        // jwt-auth 包会在其 boot() 中把 `jwt.auth` 别名指向自带的 Authenticate 中间件，
        // 覆盖 bootstrap/app.php 注册的自建 JwtAuthenticate（含 guard claim 校验与统一 401 JSON）。
        // 这里在所有 Provider 启动完成后重新绑定别名，确保自建中间件生效。
        $this->app->booted(function () {
            $this->app['router']->aliasMiddleware(
                'jwt.auth',
                \NiceShoply\Common\Middleware\JwtAuthenticate::class
            );
        });
    }
}
