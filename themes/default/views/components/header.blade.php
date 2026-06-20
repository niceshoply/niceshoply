{{--
================================================================================
【文件说明】
  前台顶部导航栏组件（Header）。
  渲染网站的完整页头，包括：语言切换、货币切换、Logo、PC 端主导航菜单、
  搜索框、会员账户入口、收藏夹、购物车图标，以及移动端 Offcanvas 抽屉菜单。

  在布局文件中通过 Blade 组件标签调用：
    <x-front-header />

【注册方式】
  FrontServiceProvider 中以别名 "front-header" 注册：
    Blade::component('front-header', Components\Header::class);
  对应的组件类为 App\...\Components\Header（或主题覆盖类），
  组件类可通过插件钩子 "front.header.component.class" 进行替换：
    fire_hook_filter('front.header.component.class', Components\Header::class)

【可用变量 / Props】
  组件类在 render() 中向视图注入以下变量：
  - $currentLocale    — 当前激活的语言对象（含 code、name、image 属性）；若未启用多语言则为 null
  - $customer         — 当前登录的会员对象（current_customer()），未登录时为 null
  - $headerMenus      — 后台配置的顶部导航菜单数组，每项结构：
                          ['name' => '菜单名', 'url' => '链接', 'children' => [...子菜单]]
  - $favTotal         — 当前会员的收藏商品数量（整数）

  全局辅助函数（在模板中随时可用）：
  - locales()                       — 返回已启用的所有语言 Collection
  - currencies()                    — 返回已启用的所有货币 Collection
  - current_currency()              — 返回当前使用的货币对象
  - system_setting('key')           — 读取系统设置（如 telephone、front_logo）
  - front_route('name', $params)    — 生成带语言前缀的前台路由 URL
  - image_origin($path)             — 将相对路径转换为完整图片 URL
  - equal_url($url)                 — 判断当前 URL 是否与给定 URL 相同（高亮 active 用）
  - equal_route_name('name')        — 判断当前路由名称是否与给定名称相同
  - account_route('name')           — 生成会员中心路由 URL

【插件钩子】
  以下 @hookinsert 点位允许插件在对应位置插入额外 HTML 内容，请勿删除：
  - @hookinsert('layout.header.top')              — Header 最顶部（整个 header 标签之前）
  - @hookinsert('layouts.header.currency.after')  — 货币切换下拉框之后
  - @hookinsert('layouts.header.news.before')     — "News" 链接之前
  - @hookinsert('layouts.header.cart.after')      — 购物车图标之后（可插入额外图标）
  - @hookinsert('layout.header.bottom')           — Header 最底部（整个 header 标签之后）

  以下 @hookupdate 点位允许插件完整替换对应区块的内容：
  - @hookupdate('layouts.header.telephone')       — 电话号码显示区块
  - @hookupdate('layouts.header.menu.pc')         — PC 端导航菜单列表（<ul> 内部）
  - @hookupdate('layouts.header.menu.mobile')     — 移动端 Offcanvas 菜单列表

【自定义建议】
  开发新主题时，可以：
  1. 直接复制本文件到主题目录并修改 HTML 结构与样式。
  2. 若需替换整个组件类（含数据逻辑），在插件中通过钩子
     "front.header.component.class" 返回自定义类名。
  3. 导航菜单通过后台「外观 → 菜单」配置，无需修改代码。
  4. Logo 图片通过后台「设置 → 系统设置 → front_logo」配置，
     调用方式：system_setting('front_logo', 'images/logo.svg')
  5. 移动端菜单使用 Bootstrap 5 Offcanvas + Accordion 实现，
     可通过 CSS 变量 --bs-* 自定义样式。
================================================================================
--}}
@hookinsert('layout.header.top')

<header id="appHeader">
  <div class="header-top">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="language-switch d-flex align-items-center">
        @if($currentLocale && locales()->isNotEmpty())
          <div class="dropdown">
            <a class="btn dropdown-toggle" href="javascript:void(0)">
              <img src="{{ asset($currentLocale->image) }}" class="img-fluid"> {{ $currentLocale->name }}
            </a>
            <div class="dropdown-menu">
              @foreach (locales() as $locale)
                <a class="dropdown-item d-flex" href="{{ front_route('locales.switch', ['code' => $locale->code]) }}">
                  <div class="wh-20 me-2"><img src="{{ image_origin($locale['image']) }}" class="img-fluid border">
                  </div>
                  {{ $locale->name }}
                </a>
              @endforeach
            </div>
          </div>
        @endif
        @if(current_currency() && currencies()->isNotEmpty())
          <div class="dropdown {{ $currentLocale && locales()->isNotEmpty() ? 'ms-4' : '' }}">
            <a class="btn dropdown-toggle" href="javascript:void(0)">
              {{ current_currency()->name }}
            </a>
            <div class="dropdown-menu">
              @foreach (currencies() as $currency)
                <a class="dropdown-item" href="{{ front_route('currencies.switch', ['code' => $currency->code]) }}">
                  {{ $currency->name }} ({{ $currency->symbol_left }})
                </a>
              @endforeach
            </div>
          </div>
        @endif
        @hookinsert('layouts.header.currency.after')
      </div>

      <div class="top-info">
        @hookinsert('layouts.header.news.before')
        <a href="{{ front_route('articles.index') }}">News</a>

        @hookupdate('layouts.header.telephone')
        @if (system_setting('telephone'))
          <a href="tel:{{ system_setting('telephone') }}">
            <span><i class="bi bi-telephone-outbound"></i> {{ system_setting('telephone') }}</span>
          </a>
        @endif
        @endhookupdate
      </div>
    </div>
  </div>
  <div class="header-desktop">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="left">
        <h1 class="logo">
          <a href="{{ front_route('home.index') }}">
            <img src="{{ image_origin(system_setting('front_logo', 'images/logo.svg')) }}" class="img-fluid">
          </a>
        </h1>
        <div class="menu">
          <nav class="navbar navbar-expand-md navbar-light">
            <ul class="navbar-nav">
              <li class="nav-item">
                <a class="nav-link" aria-current="page"
                   href="{{ front_route('home.index') }}">{{ __('front/common.home') }}</a>
              </li>

              @hookupdate('layouts.header.menu.pc')
              @foreach ($headerMenus as $menu)
                @if ($menu['children'] ?? [])
                  <li class="nav-item">
                    <div class="dropdown">
                      @if ($menu['name'])
                        <a class="nav-link {{ (request()->route() && equal_url($menu['url'])) ? 'active' : '' }}"
                           href="{{ $menu['url'] }}">{{ $menu['name'] }}</a>
                      @endif
                      <ul class="dropdown-menu">
                        @foreach ($menu['children'] as $child)
                          @if ($child['name'])
                            <li><a class="dropdown-item" href="{{ $child['url'] }}">{{ $child['name'] }}</a></li>
                          @endif
                        @endforeach
                      </ul>
                    </div>
                  </li>
                @else
                  @if ($menu['name'])
                    <li class="nav-item">
                      <a class="nav-link {{ (request()->route() && equal_url($menu['url'])) ? 'active' : '' }}"
                         href="{{ $menu['url'] }}">{{ $menu['name'] }}</a>
                    </li>
                  @endif
                @endif
              @endforeach
              @endhookupdate
            </ul>
          </nav>
        </div>
      </div>
      <div class="right">
        <form action="{{ front_route('products.index') }}" method="get" class="search-group">
          <input type="text" class="form-control" name="keyword" placeholder="{{ __('front/common.search') }}"
                 value="{{ request('keyword') }}">
          <button type="submit" class="btn"><i class="bi bi-search"></i></button>
        </form>
        <div class="icons">
          <div class="item">
            <div class="dropdown account-icon">
              <a class="btn dropdown-toggle px-0" href="{{ front_route('account.index') }}">
                <img src="{{ asset('images/icons/account.svg') }}" class="img-fluid">
              </a>

              <div class="dropdown-menu dropdown-menu-end">
                @if ($customer)
                  <a href="{{ front_route('account.index') }}"
                     class="dropdown-item">{{ __('front/account.account') }}</a>
                  <a href="{{ front_route('account.orders.index') }}"
                     class="dropdown-item">{{ __('front/account.orders') }}</a>
                  <a href="{{ front_route('account.favorites.index') }}"
                     class="dropdown-item">{{ __('front/account.favorites') }}</a>
                  <a href="{{ front_route('account.logout') }}"
                     class="dropdown-item">{{ __('front/account.logout') }}</a>
                @else
                  <a href="{{ front_route('login.index') }}" class="dropdown-item">{{ __('front/common.login') }}</a>
                  <a href="{{ front_route('register.index') }}"
                     class="dropdown-item">{{ __('front/common.register') }}</a>
                @endif
              </div>
            </div>
          </div>
          <div class="item">
            <a href="{{ account_route('favorites.index') }}"><img src="{{ asset('images/icons/love.svg') }}"
                                                                  class="img-fluid"><span
                class="icon-quantity">{{ $favTotal }}</span></a>
          </div>
          <div class="item">
            <a href="javascript:void(0)" class="header-cart-icon" data-bs-toggle="offcanvas"
               data-bs-target="#miniCart" aria-controls="miniCart">
              <img src="{{ asset('images/icons/cart.svg') }}" class="img-fluid">
              <span class="icon-quantity">0</span>
            </a>
          </div>
          @hookinsert('layouts.header.cart.after')
        </div>
      </div>
    </div>
  </div>
  <div class="header-mobile">
    <div class="mb-icon" data-bs-toggle="offcanvas" data-bs-target="#mobile-menu-offcanvas"
         aria-controls="offcanvasExample">
      <i class="bi bi-list"></i>
    </div>

    <div class="logo">
      <a href="{{ front_route('home.index') }}">
        <img src="{{ image_origin(system_setting('front_logo', 'images/logo.svg')) }}" class="img-fluid">
      </a>
    </div>

    <a href="{{ front_route('carts.index') }}" class="header-cart-icon"><img src="{{ asset('images/icons/cart.svg') }}"
                                                                             class="img-fluid"><span
        class="icon-quantity">0</span></a>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobile-menu-offcanvas">
      <div class="offcanvas-header">
        <form action="{{ front_route('products.index') }}" method="get" class="search-group">
          <input type="text" class="form-control" name="keyword" placeholder="{{ __('front/common.search') }}"
                 value="{{ request('keyword') }}">
          <button type="submit" class="btn"><i class="bi bi-search"></i></button>
        </form>
        <a class="account-icon" href="{{ front_route('account.index') }}">
          <img src="{{ asset('images/icons/account.svg') }}" class="img-fluid">
        </a>
      </div>
      <div class="close-offcanvas" data-bs-dismiss="offcanvas"><i class="bi bi-chevron-compact-left"></i></div>
      <div class="offcanvas-body mobile-menu-wrap">
        <div class="accordion accordion-flush" id="menu-accordion">
          <div class="accordion-item">
            <div class="nav-item-text">
              <a class="nav-link {{ (request()->route() && equal_route_name('home.index')) ? 'active' : '' }}" aria-current="page"
                 href="{{ front_route('home.index') }}">{{ __('front/common.home') }}</a>
            </div>
          </div>

          @hookupdate('layouts.header.menu.mobile')
          @foreach ($headerMenus as $key => $menu)
            @if ($menu['name'])
              <div class="accordion-item">
                <div class="nav-item-text">
                  <a class="nav-link" href="{{ $menu['url'] }}"
                     data-bs-toggle="{{ !$menu['url'] ? 'collapse' : '' }}">
                    {{ $menu['name'] }}
                  </a>
                  @if (isset($menu['children']) && $menu['children'])
                    <span class="collapsed" data-bs-toggle="collapse"
                          data-bs-target="#flush-menu-{{ $key }}"><i class="bi bi-chevron-down"></i></span>
                  @endif
                </div>

                @if (isset($menu['children']) && $menu['children'])
                  <div class="accordion-collapse collapse" id="flush-menu-{{ $key }}"
                       data-bs-parent="#menu-accordion">
                    <div class="children-group">
                      <ul class="nav flex-column ul-children">
                        @foreach ($menu['children'] as $c_key => $child)
                          @if ($child['name'])
                            <li class="nav-item">
                              <a class="nav-link" href="{{ $child['url'] }}">{{ $child['name'] }}</a>
                            </li>
                          @endif
                        @endforeach
                      </ul>
                    </div>
                  </div>
                @endif
              </div>
            @endif
          @endforeach
          @endhookupdate

        </div>
      </div>
    </div>
  </div>
</header>

@hookinsert('layout.header.bottom')