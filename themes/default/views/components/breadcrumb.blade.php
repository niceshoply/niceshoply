{{--
================================================================================
【文件说明】
  面包屑导航组件（Breadcrumb）。
  渲染当前页面的层级路径导航，帮助用户了解所在位置并快速返回上级页面。
  第一项（首页）显示房屋图标（bi-house-door-fill），其余项显示文字链接。
  最后一项（当前页）无链接，标记为 active 状态。
  在商品列表页（products.index）还可选择性地显示一个移动端「筛选」按钮。

  在页面模板中通过 Blade 组件标签调用：
    <x-front-breadcrumb />
  或传入额外 prop 显示筛选按钮：
    <x-front-breadcrumb :show-filter="true" />

【注册方式】
  FrontServiceProvider 中以别名 "front-breadcrumb" 注册：
    Blade::component('front-breadcrumb', Components\Breadcrumb::class);

【可用变量 / Props】
  组件类在 render() 中向视图注入以下变量：
  - $breadcrumbs  — 面包屑数组，由当前页面控制器或服务层构建并注入，每项结构：
      [
        'title'        => '完整标题',           // 原始标题文字
        'display_title'=> '截断后的显示标题',   // 可选，超长时截断显示
        'full_title'   => '完整标题（tooltip）',// 可选，用于 HTML title 属性悬浮提示
        'url'          => 'https://...',        // 有 URL 则渲染为链接，为空则为当前页（active）
      ]
      示例（商品详情页）：
        [
          ['title' => '首页', 'url' => 'https://example.com/'],
          ['title' => '手机', 'url' => 'https://example.com/categories/phones'],
          ['title' => 'iPhone 16', 'url' => ''],   // 当前页，无链接
        ]

  组件 Props（从父模板传入）：
  - $showFilter   — 布尔值（默认 false），为 true 时在移动端显示「筛选」按钮，
                    按钮绑定 id="toggleFilterSidebar"，由商品列表页的 JS 监听

【插件钩子】
  本组件内部暂无 @hookinsert 点位。

【自定义建议】
  开发新主题时，可以：
  1. 修改 .breadcrumb-wrap 的背景色、内边距等样式以匹配主题设计。
  2. 首页图标（bi-house-door-fill）可替换为文字「首页」或其他 SVG 图标。
  3. display_title 由组件类自动截断长标题，截断规则可在 Breadcrumb::class 中调整。
  4. 若面包屑数组为空（count($breadcrumbs) == 0），容器会切换为居中布局，
     可以考虑直接 v-if 隐藏整个组件。
  5. 移动端筛选按钮（#toggleFilterSidebar）的 JS 逻辑在商品列表页的 JS 文件中，
     修改时需同步更新对应事件监听。
================================================================================
--}}
<div class="breadcrumb-wrap">
  <div class="container {{ count($breadcrumbs) > 0 ? 'd-flex justify-content-start align-items-center' : 'justify-content-center' }}">
    <ul class="breadcrumb mb-0">
      @foreach ($breadcrumbs as $index=>$breadcrumb)
        @if (isset($breadcrumb['url']) && $breadcrumb['url'])
          <li>
            @if($index == 0)
              <i class="bi bi-house-door-fill home-icon"></i>
            @endif
            <a href="{{ $breadcrumb['url'] }}" @if(isset($breadcrumb['full_title'])) title="{{ $breadcrumb['full_title'] }}" @endif>
              {{ $breadcrumb['display_title'] ?? $breadcrumb['title'] }}
            </a>
          </li>
        @else
          <li class="breadcrumb-item active" aria-current="page" @if(isset($breadcrumb['full_title'])) title="{{ $breadcrumb['full_title'] }}" @endif>
            {{ $breadcrumb['display_title'] ?? $breadcrumb['title'] }}
          </li>
        @endif
      @endforeach
    </ul>

    @if (count($breadcrumbs) > 0 && isset($showFilter) && $showFilter)
      <div class="breadcrumb-filter-btn d-block d-md-none">
        <button class="btn btn-outline-primary btn-sm d-flex align-items-center" id="toggleFilterSidebar">
          <i class="bi bi-funnel me-1"></i>
          <span class="filter-text">{{ __('front/common.filter') }}</span>
        </button>
      </div>
    @endif
  </div>
</div>
