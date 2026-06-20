<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

/**
 * ============================================================
 * 前台模板查找机制总览
 * ============================================================
 *
 * 本文件是前台（店铺展示端）的核心 ServiceProvider，
 * 负责定义"当没有自定义主题模板时，系统从哪里加载原始默认模板"。
 *
 * 【模板目录结构与查找优先级】
 *
 * 当调用 view('home') 或 nice_view('home') 时，
 * Laravel 的 FileViewFinder 会按以下顺序逐一查找，找到即停止：
 *
 *   优先级 1（最高）：用户自定义主题模板目录
 *     路径：{项目根}/themes/{theme_code}/views/
 *     条件：system_setting('theme') 有值，且该目录存在
 *     例如：themes/default/views/home.blade.php
 *     说明：通过 php artisan inno:publish-theme 发布后生成
 *
 *   优先级 2（回退）：包内置原始默认模板目录  ← 无自定义主题时调用的文件在这里
 *     路径：niceshoply/front/resources/views/
 *     对应本文件 __DIR__ 的相对路径：__DIR__.'/../resources/views'
 *     条件：始终存在，作为最终回退保障
 *     例如：niceshoply/front/resources/views/home.blade.php
 *
 *   优先级 3（最低）：Laravel 应用层视图目录
 *     路径：{项目根}/resources/views/
 *     说明：主要存放应用级视图（如 console 后台），前台一般不命中
 *
 * 【原始默认模板的完整路径】
 *
 *   niceshoply/front/resources/views/
 *   ├── layouts/
 *   │   └── app.blade.php          主布局文件（header/footer/scripts 结构）
 *   ├── home.blade.php             首页模板
 *   ├── products/
 *   │   ├── index.blade.php        商品列表页
 *   │   └── show.blade.php         商品详情页
 *   ├── categories/
 *   │   └── show.blade.php         分类页
 *   ├── cart/                      购物车相关页
 *   ├── checkout/                  结账流程相关页
 *   ├── account/                   用户中心相关页
 *   ├── pages/
 *   │   └── show.blade.php         CMS 内容页
 *   ├── errors/
 *   │   └── 404.blade.php          404 错误页
 *   ├── shared/                    公共局部模板（组件片段）
 *   ├── css/                       内联样式（供模板引用）
 *   ├── js/                        内联脚本（供模板引用）
 *   └── config.json                主题元数据（code: "default"）
 *
 * 【发布命令】
 *
 *   php artisan inno:publish-theme
 *   等同于：
 *   php artisan vendor:publish --provider='NiceShoply\Front\FrontServiceProvider' --tag=views
 *
 *   执行后会将 niceshoply/front/resources/ 整包复制到 {项目根}/themes/default/，
 *   之后对 themes/default/views/ 下的文件进行修改即可自定义主题，
 *   原始包内文件不会被覆盖，仍作为缺失文件的回退来源。
 *
 * 【视图渲染入口】
 *
 *   所有前台控制器均通过全局辅助函数 nice_view($view, $data) 渲染视图，
 *   该函数在触发 ViewHook 插件钩子后调用 Laravel 原生 view()，
 *   定义位于：niceshoply/common/helpers.php
 *
 * ============================================================
 */

namespace NiceShoply\Front;

use Exception;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;
use NiceShoply\Common\Middleware\ContentFilterHook;
use NiceShoply\Common\Middleware\EventActionHook;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Front\Middleware\CustomerAuthentication;
use NiceShoply\Front\Middleware\GlobalFrontData;
use NiceShoply\Front\Middleware\MaintenanceMode;
use NiceShoply\Front\Middleware\SetFrontLocale;

/**
 * 前台服务提供者
 *
 * 负责前台（店铺展示端）的全部启动工作，包括：
 *   - 路由注册（含多语言前缀路由）
 *   - 翻译文件加载
 *   - 认证守卫配置（customer / customer_api）
 *   - 主题模板发布注册
 *   - 自定义 FileViewFinder（主题路径 → 内置默认路径 的查找链）
 *   - Blade 视图组件注册（<x-front-header>、<x-nice-products> 等）
 *   - {nice:xxx} 自定义标签预编译器
 *   - 主题语言包加载
 */
class FrontServiceProvider extends ServiceProvider
{
    /**
     * 启动前台服务提供者。
     *
     * 仅在完成系统安装（存在 install.lock）后执行，
     * 未安装时直接返回以防止在安装向导阶段报错。
     *
     * 启动顺序说明：
     *   1. load_settings()           — 从数据库加载系统配置到内存（theme、locale 等）
     *   2. registerWebRoutes()       — 注册前台路由及中间件组
     *   3. loadTranslations()        — 加载包内 lang/ 翻译文件，命名空间 front::
     *   4. registerGuard()           — 注册 customer 认证守卫
     *   5. publishViewTemplates()    — 注册 vendor:publish 发布映射（不立即复制文件）
     *   6. loadThemeViewPath()       — 重建 view.finder，注入主题路径查找链（核心）
     *   7. View::addExtension(html)  — 使 .html 文件也可被 Blade 解析
     *   8. loadViewComponents()      — 注册 Blade 组件
     *   9. loadThemeTranslations()   — 加载当前主题自带的语言包
     *  10. registerNiceTagPrecompiler() — 注册 {nice:xxx} 自定义标签转换器
     *
     * @return void
     * @throws Exception
     */
    public function boot(): void
    {
        // publishViewTemplates 必须在安装锁检查之前执行，
        // 因为 vendor:publish / inno:publish-theme 在未安装状态下也需要能正确找到发布映射。
        $this->publishViewTemplates();

        if (! has_install_lock()) {
            return;
        }

        load_settings();
        $this->registerWebRoutes();
        $this->loadThemeRoutes();
        $this->loadTranslations();
        $this->registerGuard();
        $this->loadThemeViewPath();

        View::addExtension('html', 'blade');

        $this->loadViewComponents();
        $this->loadThemeTranslations();
        $this->registerNiceTagPrecompiler();
        $this->bootTheme();
    }

    /**
     * @return void
     */
    public function register(): void
    {
        app('router')->aliasMiddleware('customer_auth', CustomerAuthentication::class);
    }

    /**
     * Register guard for frontend.
     */
    protected function registerGuard(): void
    {
        Config::set('auth.providers.customer', [
            'driver' => 'eloquent',
            'model'  => Customer::class,
        ]);

        Config::set('auth.guards.customer', [
            'driver'   => 'session',
            'provider' => 'customer',
        ]);

        // JWT API guard for customer
        Config::set('auth.guards.customer_api', [
            'driver'   => 'jwt',
            'provider' => 'customer',
        ]);
    }

    /**
     * Register admin front routes.
     *
     * @return void
     * @throws Exception
     */
    protected function registerWebRoutes(): void
    {
        $router      = $this->app['router'];
        $middlewares = [
            SetFrontLocale::class,
            EventActionHook::class,
            ContentFilterHook::class,
            GlobalFrontData::class,
            MaintenanceMode::class,
        ];

        foreach ($middlewares as $middleware) {
            $router->pushMiddlewareToGroup('front', $middleware);
        }

        Route::middleware('front')
            ->name('front.')
            ->group(function () {
                $this->loadRoutesFrom(realpath(__DIR__.'/../routes/root.php'));
            });

        $locales = locales();
        if (hide_url_locale() || $locales->isEmpty()) {
            Route::middleware('front')
                ->name('front.')
                ->group(function () {
                    $this->loadRoutesFrom(realpath(__DIR__.'/../routes/web.php'));
                });
        } else {
            foreach ($locales as $locale) {
                Route::middleware('front')
                    ->prefix($locale->code)
                    ->name($locale->code.'.front.')
                    ->group(function () {
                        $this->loadRoutesFrom(realpath(__DIR__.'/../routes/web.php'));
                    });
            }
        }
    }

    /**
     * Register front language
     * @return void
     */
    protected function loadTranslations(): void
    {
        if (! is_dir(__DIR__.'/../lang')) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'front');
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/front'),
        ], 'lang');
    }

    /**
     * 注册默认主题的发布映射。
     *
     * 本方法仅向 Laravel 的 publishes 系统登记一条映射关系，
     * 不会立即复制文件。只有执行以下命令时才真正复制：
     *
     *   php artisan inno:publish-theme
     *   等同于：
     *   php artisan vendor:publish --provider='NiceShoply\Front\FrontServiceProvider' --tag=views
     *
     * 发布内容：
     *   源目录（包内原始模板）：niceshoply/front/resources/
     *     ├── views/     — Blade 模板文件
     *     ├── css/       — 主题样式文件
     *     ├── js/        — 主题脚本文件
     *     └── config.json — 主题元数据（name、code、version 等）
     *
     *   目标目录（可自定义区域）：{项目根}/themes/default/
     *     发布后可随意修改其中的模板，修改不影响包内原始文件。
     *     若某文件在 themes/default/views/ 中不存在，
     *     系统仍会自动回退到包内 niceshoply/front/resources/views/ 中的对应文件。
     *
     * @return void
     */
    protected function publishViewTemplates(): void
    {
        // 原始模板源：包内 niceshoply/front/resources/ 整个目录
        $originViewPath = __DIR__.'/../resources';

        // 发布目标：项目根目录下的 themes/default/ 文件夹（即"默认主题"的可编辑副本）
        $customViewPath = base_path('themes/default');

        $this->publishes([
            $originViewPath => $customViewPath,
        ], 'views');
    }

    /**
     * 重建 view.finder（视图文件查找器），注入主题路径查找链。
     *
     * 这是整个主题系统的核心：通过覆盖 Laravel 容器中的 view.finder 单例，
     * 将模板查找路径按"自定义主题 → 内置原始默认 → Laravel 应用层"的顺序排列。
     *
     * 【查找链说明】
     *
     *   路径 1（最高优先级）：用户自定义主题模板
     *     {项目根}/themes/{theme_code}/views/
     *     条件：system_setting('theme') 返回非空值，且该目录在磁盘上存在
     *     用途：存放用户通过 inno:publish-theme 发布并二次修改的自定义模板
     *     示例：themes/default/views/home.blade.php
     *
     *   路径 2（回退，无自定义主题时必然命中）：包内原始默认模板  ← 关键
     *     niceshoply/front/resources/views/
     *     由 realpath(__DIR__.'/../resources/views') 解析
     *     __DIR__ = niceshoply/front/src/
     *     实际路径 = niceshoply/front/resources/views/
     *     特点：始终存在于包内，任何视图名均可在此找到对应文件
     *     示例：niceshoply/front/resources/views/home.blade.php
     *
     *   路径 3（最低优先级）：Laravel 应用层视图
     *     {项目根}/resources/views/
     *     来源：config/view.php 中 'paths' 配置（框架默认值）
     *     用途：console 后台、应用级页面，与前台主题无关
     *
     * 【查找示例】
     *
     *   调用 nice_view('home') 时，FileViewFinder 按顺序搜索：
     *     1. themes/default/views/home.blade.php          → 若存在则使用
     *     2. niceshoply/front/resources/views/home.blade.php → 回退使用（原始模板）
     *     3. resources/views/home.blade.php               → 前台通常不命中
     *
     * 【为何用 singleton 而非直接 bind】
     *
     *   view.finder 需要在整个请求生命周期内保持同一实例，
     *   避免多次重建导致已扫描的视图路径缓存失效。
     *
     * @return void
     */
    protected function loadThemeViewPath(): void
    {
        $this->app->singleton('view.finder', function ($app) {
            $themePaths = [];

            // 尝试加载用户自定义主题的视图目录（优先级最高）
            // system_setting('theme') 从数据库配置表读取当前激活的主题 code
            if ($theme = system_setting('theme')) {
                $themeViewPath = base_path("themes/{$theme}/views");
                // 只有目录真实存在时才加入查找链，防止 FileViewFinder 因路径不存在而报错
                if (is_dir($themeViewPath)) {
                    $themePaths[] = $themeViewPath;
                }
            }

            // 始终将包内原始默认模板目录追加到查找链（回退保障）
            // 无论是否有自定义主题，此路径都会作为最终回退来源
            // 实际解析路径：niceshoply/front/resources/views/
            $themePaths[] = realpath(__DIR__.'/../resources/views');

            // 获取 Laravel 框架本身的视图路径（来自 config/view.php 的 paths 配置）
            // 默认为 [{项目根}/resources/views]，将其追加在最后（最低优先级）
            $viewPaths = $app['config']['view.paths'];
            $viewPaths = array_merge($themePaths, $viewPaths);

            // 用合并后的路径列表构建新的 FileViewFinder 实例，替换框架默认实例
            return new FileViewFinder($app['files'], $viewPaths);
        });
    }

    /**
     * Load view components.
     *
     * @return void
     */
    protected function loadViewComponents(): void
    {
        // Register basic components
        $this->loadViewComponentsAs('front', [
            'breadcrumb' => Components\Breadcrumb::class,
            'review'     => Components\Review::class,
        ]);

        // Register Nice tag components: {nice:slideshow}, {nice:products}, etc.
        $this->loadViewComponentsAs('nice', [
            'slideshow'    => Components\Nice\Slideshow::class,
            'products'     => Components\Nice\Products::class,
            'categories'   => Components\Nice\Categories::class,
            'articles'     => Components\Nice\Articles::class,
            'pages'        => Components\Nice\Pages::class,
            'hot-products' => Components\Nice\HotProducts::class,
        ]);

        // Delay registration of header and footer components to ensure plugin hooks take effect
        $this->app->booted(function () {
            $headerClass = fire_hook_filter('front.header.component.class', Components\Header::class);
            $footerClass = fire_hook_filter('front.footer.component.class', Components\Footer::class);

            $this->loadViewComponentsAs('front', [
                'header' => $headerClass,
                'footer' => $footerClass,
            ]);
        });
    }

    /**
     * Nice tag directives that map to Blade directives (not components).
     */
    private const NICE_DIRECTIVES = [
        'extends', 'section', 'yield', 'push', 'stack',
        'if', 'elseif', 'else', 'foreach', 'for',
        'include', 'hook', 'csrf', 'php', 'json', 'isset', 'empty',
    ];

    /**
     * Closing tag to Blade end-directive mapping.
     */
    private const NICE_CLOSING_MAP = [
        'section' => '@endsection',
        'push'    => '@endpush',
        'if'      => '@endif',
        'foreach' => '@endforeach',
        'for'     => '@endfor',
        'isset'   => '@endisset',
        'empty'   => '@endempty',
        'php'     => ' ?>',
    ];

    /**
     * Register {nice:xxx} tag precompiler.
     *
     * Supports theme templates in both .blade.php and .html format.
     * Processing order:
     *   1. Comments:   {-- comment --}           → (removed)
     *   2. Closing:    {/nice:section}            → @endsection
     *   3. Directives: {nice:if $x}               → @if($x)
     *   4. Components: {nice:products limit="8"}   → <x-nice-products limit="8" />
     */
    protected function registerNiceTagPrecompiler(): void
    {
        $directivePattern = implode('|', self::NICE_DIRECTIVES);

        Blade::precompiler(function (string $string) use ($directivePattern) {
            // Round 1: Comments  {-- ... --} → removed entirely
            $string = preg_replace('/(?<!\{)\{--(.*?)--\}/s', '', $string);

            // Round 2: Closing tags  {/nice:xxx} → @endxxx
            $string = preg_replace_callback(
                '/\{\/nice:([\w-]+)\}/',
                fn ($m) => self::NICE_CLOSING_MAP[$m[1]] ?? '@end'.$m[1],
                $string
            );

            // Round 3: Directive tags  {nice:directive expr} → @directive(expr)
            $string = preg_replace_callback(
                '/\{nice:('.$directivePattern.')\b(.*?)\}/s',
                fn ($m) => $this->compileNiceDirective($m[1], trim($m[2])),
                $string
            );

            // Round 4: Component tags  {nice:xxx attr="val"} → <x-nice-xxx attr="val" />
            $string = preg_replace_callback(
                '/\{nice:([\w-]+)((?:\s+[\w:-]+(?:=(?:"[^"]*"|\'[^\']*\'|[^\s}]*))?)*)\s*\}/',
                function ($matches) {
                    $component = $matches[1];
                    $rawAttrs  = trim($matches[2] ?? '');

                    $rawAttrs = preg_replace_callback(
                        '/([\w:-]+)=([^\s"\'}\]][^\s}]*)/',
                        fn ($m) => $m[1].'="'.$m[2].'"',
                        $rawAttrs
                    );

                    return '<x-nice-'.$component.' '.$rawAttrs.' />';
                },
                $string
            );

            return $string;
        });
    }

    private function compileNiceDirective(string $directive, string $body): string
    {
        return match ($directive) {
            'extends' => $this->compileNiceExtends($body),
            'section' => $this->compileNiceSection($body),
            'yield'   => $this->compileNiceYield($body),
            'push'    => $this->compileNicePush($body),
            'stack'   => $this->compileNiceStack($body),
            'include' => $this->compileNiceInclude($body),
            'hook'    => $this->compileNiceHook($body),
            'else'    => '@else',
            'csrf'    => '@csrf',
            'php'     => '<?php ',
            'if', 'elseif', 'foreach', 'for', 'isset', 'empty', 'json' => '@'.$directive.'('.$body.')',
            default => '@'.$directive.'('.$body.')',
        };
    }

    /**
     * {nice:extends layout="app"} → @extends('layouts.app')
     */
    private function compileNiceExtends(string $body): string
    {
        if (preg_match('/layout=["\']?([^"\'}\s]+)["\']?/', $body, $m)) {
            return "@extends('layouts.".$m[1]."')";
        }

        $name = trim($body, '"\'');

        return $name ? "@extends('".$name."')" : '';
    }

    /**
     * {nice:section name="content"}            → @section('content')
     * {nice:section name="body-class" value="page-home"} → @section('body-class', 'page-home')
     */
    private function compileNiceSection(string $body): string
    {
        $name  = '';
        $value = null;

        if (preg_match('/name=["\']([^"\']*)["\']/', $body, $m)) {
            $name = $m[1];
        }
        if (preg_match('/value=["\']([^"\']*)["\']/', $body, $m)) {
            $value = $m[1];
        }

        if (! $name) {
            return '';
        }

        if ($value !== null) {
            return "@section('".$name."', '".$value."')";
        }

        return "@section('".$name."')";
    }

    /**
     * {nice:yield name="content"}                                    → @yield('content')
     * {nice:yield name="title" default="Page Title"}                 → @yield('title', 'Page Title')
     * {nice:yield name="title" default=system_setting_locale('x')}   → @yield('title', system_setting_locale('x'))
     */
    private function compileNiceYield(string $body): string
    {
        $name = '';
        if (preg_match('/name=["\']([^"\']*)["\']/', $body, $m)) {
            $name = $m[1];
        }
        if (! $name) {
            return '';
        }

        if (preg_match('/default="([^"]*)"/', $body, $dm)) {
            return "@yield('".$name."', '".$dm[1]."')";
        }
        if (preg_match('/default=(.+)$/', $body, $dm)) {
            return "@yield('".$name."', ".trim($dm[1]).')';
        }

        return "@yield('".$name."')";
    }

    /**
     * {nice:push name="header"} → @push('header')
     */
    private function compileNicePush(string $body): string
    {
        if (preg_match('/name=["\']([^"\']*)["\']/', $body, $m)) {
            return "@push('".$m[1]."')";
        }

        return '';
    }

    /**
     * {nice:stack name="header"} → @stack('header')
     */
    private function compileNiceStack(string $body): string
    {
        if (preg_match('/name=["\']([^"\']*)["\']/', $body, $m)) {
            return "@stack('".$m[1]."')";
        }

        return '';
    }

    /**
     * {nice:include "shared.product"}                    → @include('shared.product')
     * {nice:include "shared.blog" item=$article}         → @include('shared.blog', ['item' => $article])
     * {nice:include "shared.card" title="Hello" size=3}  → @include('shared.card', ['title' => 'Hello', 'size' => 3])
     */
    private function compileNiceInclude(string $body): string
    {
        if (! preg_match('/["\']([^"\']+)["\']/', $body, $pathMatch)) {
            return '';
        }

        $path      = $pathMatch[1];
        $remaining = trim(substr($body, strlen($pathMatch[0])));

        if (empty($remaining)) {
            return "@include('".$path."')";
        }

        $params = [];
        preg_match_all('/([\w]+)=(\$[\w>.-]+|"[^"]*"|\'[^\']*\'|[\w]+)/', $remaining, $kvMatches, PREG_SET_ORDER);

        foreach ($kvMatches as $kv) {
            $key = $kv[1];
            $val = $kv[2];

            if (str_starts_with($val, '$')) {
                $params[] = "'".$key."' => ".$val;
            } elseif (str_starts_with($val, '"') || str_starts_with($val, "'")) {
                $params[] = "'".$key."' => ".str_replace("'", '"', $val);
            } else {
                $params[] = "'".$key."' => '".$val."'";
            }
        }

        if (empty($params)) {
            return "@include('".$path."')";
        }

        return "@include('".$path."', [".implode(', ', $params).'])';
    }

    /**
     * {nice:hook "home.content.top"} → @hookinsert('home.content.top')
     * {nice:hook home.content.top}   → @hookinsert('home.content.top')
     */
    private function compileNiceHook(string $body): string
    {
        if (preg_match('/["\']([^"\']+)["\']/', $body, $m)) {
            return "@hookinsert('".$m[1]."')";
        }

        $name = trim($body);

        return $name ? "@hookinsert('".$name."')" : '';
    }

    /**
     * 加载当前主题自定义路由（IMP-13）。
     *
     * 主题可在自身目录下声明独立路由，扩展前台页面：
     *   - themes/{theme}/routes/root.php  ：无语言前缀的根路由
     *   - themes/{theme}/routes/front.php ：带多语言前缀的前台路由
     *
     * 路由命名与中间件与系统前台路由保持一致（front 中间件组、front. 名称前缀，
     * 多语言时为 {code}.front. 前缀），便于主题路由复用系统能力。
     *
     * @return void
     */
    protected function loadThemeRoutes(): void
    {
        $currentTheme = system_setting('theme');
        if (! $currentTheme) {
            return;
        }

        $themeBasePath = base_path("themes/{$currentTheme}");

        // 根路由（无语言前缀）
        $rootRoutePath = "{$themeBasePath}/routes/root.php";
        if (is_file($rootRoutePath)) {
            Route::middleware('front')
                ->name('front.')
                ->group(function () use ($rootRoutePath) {
                    $this->loadRoutesFrom($rootRoutePath);
                });
        }

        // 前台路由（按系统语言策略处理前缀）
        $frontRoutePath = "{$themeBasePath}/routes/front.php";
        if (is_file($frontRoutePath)) {
            $locales = locales();
            if (hide_url_locale() || $locales->isEmpty()) {
                Route::middleware('front')
                    ->name('front.')
                    ->group(function () use ($frontRoutePath) {
                        $this->loadRoutesFrom($frontRoutePath);
                    });
            } else {
                foreach ($locales as $locale) {
                    Route::middleware('front')
                        ->prefix($locale->code)
                        ->name($locale->code.'.front.')
                        ->group(function () use ($frontRoutePath) {
                            $this->loadRoutesFrom($frontRoutePath);
                        });
                }
            }
        }
    }

    /**
     * 加载当前主题的运行期启动文件（IMP-13）。
     *
     * 主题可通过 themes/{theme}/setup/boot.php 在运行期注册 Hook
     * （add_hook_filter / add_hook_action）或绑定服务，实现主题级扩展。
     * 该文件需返回一个可调用对象（闭包），与 Demo Seeder 的加载模式一致。
     *
     * @return void
     */
    protected function bootTheme(): void
    {
        $currentTheme = system_setting('theme');
        if (! $currentTheme) {
            return;
        }

        $bootFile = base_path("themes/{$currentTheme}/setup/boot.php");
        if (! is_file($bootFile)) {
            return;
        }

        $boot = require $bootFile;
        if (is_callable($boot)) {
            $boot();
        }
    }

    /**
     * Load theme languages.
     *
     * @return void
     */
    protected function loadThemeTranslations(): void
    {
        $currentTheme = system_setting('theme');
        if (! $currentTheme) {
            return;
        }

        $themeLangPath = base_path("themes/{$currentTheme}/lang");
        if (! is_dir($themeLangPath)) {
            return;
        }

        $this->loadTranslationsFrom($themeLangPath, "theme-{$currentTheme}");
    }
}
