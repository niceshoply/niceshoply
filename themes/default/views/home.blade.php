{{--
  ============================================================
  【文件说明】
    前台首页视图模板。
    渲染电商网站的首页内容，包含：轮播图（Swiper）、首页分类导航、
    精选商品标签页、热门商品分组标签页、最新博客/资讯列表。

  【对应路由/控制器】
    路由名称：front.home.index（带语言前缀，如 /en、/zh-CN 等）
    控制器：NiceShoply\Front\Http\Controllers\HomeController@index
    视图调用：nice_view('home', $data)

  【可用变量（由控制器注入）】
    $slideshow        — 轮播图数据，数组格式。每项结构：
                        [
                          'image' => ['zh-CN' => 'path/to/img.jpg', 'en' => '...'],  // 多语言图片路径
                          'link'  => 'https://...'  // 点击跳转链接，空字符串时不跳转
                        ]
                        使用 front_locale_code() 取当前语言对应的图片。
                        若某语言无图片（值为 false/null），则该 slide 不渲染。

    $home_categories  — 首页分类导航数据，数组格式。每项结构：
                        [
                          'name'  => '分类名称',
                          'url'   => '分类链接',
                          'image' => '分类图片 URL（已处理为完整 URL）'
                        ]
                        为空时整个分类区块不渲染。

    $tab_products     — 精选商品标签组，数组格式。每项结构：
                        [
                          'tab_title' => 'Tab 标签文字',
                          'products'  => [ ...商品对象数组... ]
                        ]
                        商品对象通过 @include('shared.product') 渲染，
                        shared.product 使用变量 $product。

    $hot_products     — 热门商品分组数组，每项结构：
                        [
                          'category_name' => '分类名称（Tab 标签文字）',
                          'products'      => [ ...商品对象数组... ]
                        ]
                        当只有 1 个分组时不显示 Tab 导航。
                        为空时整个热门商品区块不渲染。

    $news             — 博客/资讯文章数组，每项通过 @include('shared.blog', ['item' => $new]) 渲染。

  【Sections/Blocks】
    @section('body-class', 'page-home') — 为 <body> 添加 'page-home' 类，可用于首页专属 CSS 样式
    @section('content')                 — 注入页面主体内容到 layouts.app 的 #appContent 区域

  【额外资源（@push('header')）】
    swiper-bundle.min.js  — Swiper 轮播图 JS
    swiper-bundle.min.css — Swiper 轮播图 CSS
    （仅首页加载，避免全局引入影响性能）

  【包含的局部模板】
    @include('shared.product')            — 商品卡片（变量：$product，商品对象）
    @include('shared.blog', ['item'=>...]) — 博客/资讯卡片（变量：$item）

  【插件钩子（请勿删除）】
    @hookinsert('home.content.top')    — 首页内容最顶部，插件可在此插入横幅、公告条等
    @hookinsert('home.swiper.after')   — 轮播图之后，插件可在此插入广告位、活动横幅等
    @hookinsert('home.content.bottom') — 首页内容最底部，插件可在此插入推荐模块等

  【Swiper 轮播图配置说明】
    使用 Swiper.js，选择器为 #module-swiper-1。
    默认配置：无限循环、分页点可点击、自动播放（2500ms 间隔，交互后暂停）。
    如需修改自动播放速度，调整 delay: 2500 的值。

  【隐藏区块说明（@if(0)...@endif）】
    代码中有两处被 @if(0) 包裹的横幅区块（Banner 示例），处于禁用状态，
    这些是演示用的 Demo 横幅，开发主题时可参考其结构或删除。

  【翻译键（i18n）】
    __('front/home.feature_product')       — 精选商品区块标题
    __('front/home.feature_product_text')  — 精选商品区块副标题
    __('front/home.hot_products')          — 热门商品区块标题（回退到 console/setting.hot_products）
    __('front/home.hot_products_text')     — 热门商品区块副标题
    __('front/home.news_blog')             — 博客/资讯区块标题
    __('front/home.news_blog_text')        — 博客/资讯区块副标题

  【CSS 模块类名说明（可在主题 CSS 中覆盖）】
    .module-content       — 首页所有模块的外层容器
    .module-line          — 每个独立模块区块（上下间距由此控制）
    .module-swiper        — Swiper 轮播图容器
    .module-home-categories — 首页分类导航区块
    .module-product-tab   — 商品标签页模块（精选/热门共用）
    .module-title-wrap    — 区块标题包裹层
    .module-title         — 区块主标题
    .module-sub-title     — 区块副标题
    .module-banner-1      — 全宽横幅区块（当前禁用，可启用）
    .module-banner-2      — 两栏横幅区块（当前禁用，可启用）

  【自定义建议】
    - 在自定义主题中复制此文件到 themes/{code}/views/home.blade.php 进行覆盖。
    - 新增区块时，放在 @hookinsert 钩子附近，并包裹在 <section class="module-line"> 中
      以保持统一间距。
    - 轮播图样式（高度、切换效果等）通过覆盖 .module-swiper 相关 CSS 实现。
    - 如需给首页添加专属 CSS/JS，使用 @push('header') 或 @push('footer')。
    - 分类导航图片建议尺寸：正方形，最小 200×200px。
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-home')

@push('header')
  <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
@endpush

@section('content')

  @hookinsert('home.content.top')

  <section class="module-content">
    @if ($slideshow)
      <section class="module-line">
        <div class="swiper" id="module-swiper-1">
          <div class="module-swiper swiper-wrapper">
            @foreach ($slideshow as $slide)
              @if ($slide['image'][front_locale_code()] ?? false)
                <div class="swiper-slide">
                  <a href="{{ $slide['link'] ?: 'javascript:void(0)' }}"><img
                      src="{{ image_origin($slide['image'][front_locale_code()]) }}" class="img-fluid"></a>
                </div>
              @endif
            @endforeach
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </section>
      <script>
        var swiper = new Swiper('#module-swiper-1', {
          loop: true,
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
          autoplay: {
            delay: 2500,
            disableOnInteraction: true,
          },
        });
      </script>
    @endif

    @hookinsert('home.swiper.after')

    @if (!empty($home_categories))
      <section class="module-line">
        <div class="module-home-categories">
          <div class="container">
            <div class="row gx-3 gx-lg-4 justify-content-center">
              @foreach ($home_categories as $cat)
                <div class="col-4 col-md-3 col-lg-2 mb-3">
                  <a href="{{ $cat['url'] }}" class="d-block text-center text-decoration-none">
                    @if ($cat['image'])
                      <div class="mb-2">
                        <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}" class="img-fluid rounded">
                      </div>
                    @endif
                    <div class="text-sm fw-medium text-dark">{{ $cat['name'] }}</div>
                  </a>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </section>
    @endif

    @if (0)
      <section class="module-line">
        <div class="module-banner-2">
          <div class="container">
            <div class="row">
              <div class="col-12 col-md-4 mb-2 mb-lg-0">
                <a href=""><img src="{{ asset('images/demo/banner/banner-3.jpg') }}" class="img-fluid"></a>
              </div>
              <div class="col-12 col-md-8">
                <a href=""><img src="{{ asset('images/demo/banner/banner-4.jpg') }}" class="img-fluid"></a>
              </div>
            </div>
          </div>
        </div>
      </section>
    @endif

    <section class="module-line">
      <div class="module-product-tab">
        <div class="container">
          <div class="module-title-wrap">
            <div class="module-title">{{ __('front/home.feature_product') }}</div>
            <div class="module-sub-title">{{ __('front/home.feature_product_text') }}</div>

          </div>

          <ul class="nav nav-tabs">
            @foreach ($tab_products as $item)
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                  data-bs-target="#module-product-tab-x-{{ $loop->iteration }}"
                  type="button">{{ $item['tab_title'] }}</button>
              </li>
            @endforeach
          </ul>

          <div class="tab-content">
            @foreach ($tab_products as $item)
              <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}"
                id="module-product-tab-x-{{ $loop->iteration }}">
                <div class="row gx-3 gx-lg-4">
                  @foreach ($item['products'] as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                      @include('shared.product')
                    </div>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </section>

    @if (!empty($hot_products))
      <section class="module-line">
        <div class="module-product-tab">
          <div class="container">
            <div class="module-title-wrap">
              <div class="module-title">{{ __('front/home.hot_products', [], null) ?: __('console/setting.hot_products') }}</div>
              <div class="module-sub-title">{{ __('front/home.hot_products_text', [], null) ?: '' }}</div>
            </div>

            @if (count($hot_products) > 1)
              <ul class="nav nav-tabs">
                @foreach ($hot_products as $group)
                  <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                      data-bs-target="#hot-products-tab-{{ $loop->iteration }}"
                      type="button">{{ $group['category_name'] }}</button>
                  </li>
                @endforeach
              </ul>
            @endif

            <div class="tab-content">
              @foreach ($hot_products as $group)
                <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}"
                  id="hot-products-tab-{{ $loop->iteration }}">
                  <div class="row gx-3 gx-lg-4">
                    @foreach ($group['products'] as $product)
                      <div class="col-6 col-md-4 col-lg-3">
                        @include('shared.product')
                      </div>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </section>
    @endif

    @if (0)
      <section class="module-line">
        <div class="module-banner-1">
          <div class="container">
            <a href=""><img src="{{ asset('images/demo/banner/banner-5.jpg') }}" class="img-fluid"></a>
          </div>
        </div>
      </section>
    @endif

    <section class="module-line">
      <div class="module-product-tab">
        <div class="container">
          <div class="module-title-wrap">
            <div class="module-title">{{ __('front/home.news_blog') }}</div>
            <div class="module-sub-title">{{ __('front/home.news_blog_text') }}</div>
          </div>

          <div class="row gx-3 gx-lg-4">
            @foreach ($news as $new)
              <div class="col-6 col-md-4 col-lg-3">
                @include('shared.blog', ['item' => $new])
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </section>
  </section>

  @hookinsert('home.content.bottom')

@endsection
