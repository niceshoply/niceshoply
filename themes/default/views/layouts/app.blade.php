{{--
  ============================================================
  【文件说明】
    前台主布局模板（Layout）。
    所有普通前台页面（商品列表、商品详情、购物车、个人中心等）
    均通过 @extends('layouts.app') 继承此布局。
    该文件负责输出完整的 HTML 骨架，包含 <head> 元信息、
    全局 JS/CSS 资源、公共头部/底部、迷你购物车浮层等。

  【对应路由/控制器】
    不直接对应路由，由各子页面通过 @extends 引用。

  【可用变量（由子页面 @section 填充）】
    @yield('title')       — 页面 <title>，默认读取系统设置 meta_title
    @yield('description') — <meta description>，默认读取系统设置 meta_description
    @yield('keywords')    — <meta keywords>，默认读取系统设置 meta_keywords
    @yield('body-class')  — <body> 的额外 CSS 类，如 'page-home'、'page-product' 等
    @yield('content')     — 页面主体内容区域（必填）

  【全局 JavaScript 变量（自动注入，子页面可直接使用）】
    urls.front_api         — 前台 API 基础地址
    urls.front_base        — 前台首页地址
    urls.front_upload      — 图片上传接口地址
    urls.front_cart_add    — 加入购物车接口地址
    urls.front_cart_mini   — 迷你购物车数据接口地址
    urls.front_cart        — 购物车页面地址
    urls.front_checkout    — 结算页面地址
    urls.front_login       — 登录页面地址
    urls.front_favorites   — 收藏夹列表页面地址
    urls.front_favorite_cancel — 取消收藏接口地址
    config.isLogin         — 当前是否已登录（Boolean）
    config.currency.code   — 当前货币代码，如 'USD'
    config.currency.symbol_left  — 货币左侧符号，如 '$'
    config.currency.symbol_right — 货币右侧符号（部分货币使用）
    config.currency.decimal_place — 价格小数位数
    config.currency.rate   — 货币汇率（相对于基准货币）
    asset_url              — 静态资源根路径（用于拼接图片等）

  【全局 PHP 辅助函数（在此文件中使用）】
    system_setting('key')          — 读取系统设置，如 'favicon'、'front_logo'
    system_setting_locale('key')   — 读取当前语言的系统设置，如 'meta_title'
    front_route('name')            — 生成带语言前缀的前台路由 URL
    front_root_route('name')       — 生成根路径路由（不带语言前缀）
    front_locale_direction()       — 当前语言文字方向（'ltr' 或 'rtl'）
    current_customer()             — 当前登录会员对象（未登录返回空对象）
    current_currency()             — 当前货币对象（含 symbol_left、decimal_place 等属性）
    current_currency_code()        — 当前货币代码字符串
    image_origin($path)            — 将相对路径转为图片完整 URL
    build_asset('path')            — 获取 Vite 编译产物 URL（带内容哈希缓存失效）
    niceshoply_version()           — 返回系统版本号
    csrf_token()                   — Laravel CSRF Token（Ajax 请求需携带）

  【Sections/Blocks（子页面可覆盖/追加）】
    @yield('title')       — 覆盖页面标题
    @yield('description') — 覆盖页面描述
    @yield('keywords')    — 覆盖页面关键词
    @yield('body-class')  — 追加 body CSS 类
    @yield('content')     — 注入主体内容（必填）
    @stack('header')      — 在 </head> 前注入额外 CSS/JS（子页面用 @push('header') 追加）
    @stack('footer')      — 在 </body> 前注入额外脚本（子页面用 @push('footer') 追加）

  【包含的局部模板】
    <x-front-header />        — 公共头部组件（含导航、搜索、购物车图标等）
    <x-front-footer />        — 公共底部组件（含版权、链接等）
    @include('components.mini-cart') — 迷你购物车浮层（侧滑抽屉）

  【条件渲染说明】
    当 URL 带有 ?iframe=1 参数时，头部、底部、迷你购物车均不渲染，
    适用于将页面嵌入 iframe 展示的场景（如后台预览）。

  【插件钩子（请勿删除）】
    @hookinsert('front.layout.app.head.bottom')
      — 插件可在此向 <head> 底部注入自定义 CSS/JS/Meta 标签

  【全局 CSS/JS 资源加载顺序】
    1. bootstrap.css        — Bootstrap 样式框架
    2. app.js               — 前台核心 JS（含 Alpine.js 等，需在 jQuery 前加载）
    3. jquery-3.7.1.min.js  — jQuery
    4. layer.js             — 弹窗/提示层组件
    5. bootstrap.bundle.js  — Bootstrap JS（含 Popper.js）
    6. app.css              — 前台主样式（Vite 编译产物）

  【自定义建议】
    - 在自定义主题中复制此文件到 themes/{code}/views/layouts/app.blade.php 进行覆盖。
    - 可修改 <head> 中的资源引用以加载主题专属 CSS/JS。
    - 可替换 <x-front-header /> 和 <x-front-footer /> 为自定义头尾组件。
    - 如需全局增加统计代码（Google Analytics 等），使用 @stack('header') 或
      @hookinsert('front.layout.app.head.bottom') 注入，不要直接修改此文件。
    - 注意：@stack('header') 和 @stack('footer') 是主题扩展点，不要删除。
  ============================================================
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ front_locale_direction() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ front_route('home.index') }}">
  <title>@yield('title', system_setting_locale('meta_title', 'NiceShoply - 创新的开源电商系统 | 开源独立站系统 | Laravel 12，多语言和多货币支持'))</title>
  <meta name="description" content="@yield('description', system_setting_locale('meta_description', 'NiceShoply是一款创新的开源电子商务平台，基于Laravel 12开发，具有多语言和多货币支持的特性。它采用了基于Hook的强大而灵活的插件架构，为用户提供了丰富的定制和扩展功能。欢迎体验NiceShoply，打造属于您自己的电子商务平台！'))">
  <meta name="keywords" content="@yield('keywords', system_setting_locale('meta_keywords', 'NiceShoply, 创新, 开源, 电商, 跨境电商, 开源独立站, Laravel 12, 多语言, 多货币, Hook, 插件架构, 灵活, 强大'))">
  <meta name="generator" content="NiceShoply {{ niceshoply_version() }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="api-token" content="{{ session('front_api_token') }}">
  <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">
  <link rel="stylesheet" href="{{ build_asset('front/css/bootstrap.css') }}">
  <script src="{{ build_asset('front/js/app.js') }}"></script>
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ build_asset('front/css/app.css') }}">
  <script>
    const urls = {
      front_api: '{{ route('api.home.base') }}',
      front_base: '{{ front_route('home.index') }}',
      front_upload: '{{ front_root_route('upload.images') }}',
      front_cart_add: '{{ front_route('carts.store') }}',
      front_cart_mini: '{{ front_route('carts.mini') }}',
      front_cart: '{{ front_route('carts.index') }}',
      front_checkout: '{{ front_route('checkout.index') }}',
      front_login: '{{ front_route('login.index') }}',
      front_favorites: '{{ account_route('favorites.index') }}',
      front_favorite_cancel: '{{ account_route('favorites.cancel') }}',
    };

    const config = {
      isLogin: !!{{ current_customer()->id ?? 'null' }},
      currency: {
        code: '{{ current_currency_code() }}',
        symbol_left: '{{ current_currency()?->symbol_left ?? "$" }}',
        symbol_right: '{{ current_currency()?->symbol_right ?? "" }}',
        decimal_place: {{ current_currency()?->decimal_place ?? 2 }},
        rate: {{ current_currency()?->value ?? 1 }}
      }
    };

    const asset_url = '{{ asset('') }}';
  </script>
  @stack('header')
  @hookinsert('front.layout.app.head.bottom')
</head>

<body class="@yield('body-class')">
  @if (!request('iframe'))
    <x-front-header />
  @endif

  <div class="m-0 p-0" id="appContent">
      @yield('content')
  </div>

  @if (!request('iframe'))
    <x-front-footer />
  @endif

  @if (!request('iframe'))
    @include('components.mini-cart')
  @endif

  @stack('footer')
</body>

</html>
