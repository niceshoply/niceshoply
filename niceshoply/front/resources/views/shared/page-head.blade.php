{{--
================================================================================
【文件说明】
  页面头部横幅（Page Header / Hero Banner）局部模板 ——
  用于在分类页、文章列表页、联系我们、关于我们等内页顶部展示
  页面标题和面包屑导航（Breadcrumb）。
  样式通常是一条横跨全宽的深色或图片背景条，白色居中文字。

【引用方式】
  @include('shared.page-head', ['title' => '某页面标题'])
  ※ 必须通过第二参数数组传入 $title，否则渲染时报错。

【可用变量】
  必须由调用方传入：
    $title                  — 页面标题字符串，同时用于面包屑的末级文字。
                              通常由控制器传入或在视图中直接硬编码：
                              @include('shared.page-head', ['title' => $category->name])
                              @include('shared.page-head', ['title' => __('front/article.blog')])

  全局辅助函数：
    front_route('home.index') — 生成带语言前缀的首页 URL，用于面包屑"首页"链接

【输出内容】
  .page-head > .container 结构，包含：
  - .page-title：大标题文字（使用 $title 渲染）
  - <nav> + .breadcrumb：面包屑导航
      第一项：首页链接（含房子图标）
      最后一项（active）：当前页标题

【自定义建议】
  1. 若需要动态背景图，可在调用时额外传入 $bgImage 变量：
     @include('shared.page-head', ['title' => $title, 'bgImage' => $category->banner])
     然后在本模板中通过 style="background-image: url(...)" 引用。
  2. 面包屑目前只有两级（首页 → 当前页），如需多级面包屑（如首页 → 分类 → 子分类），
     建议传入 $breadcrumbs 数组变量并用 @foreach 循环渲染。
  3. "首页"文字目前硬编码为中文"首页"，多语言项目建议改为翻译键：
     {{ __('front/common.home') }}
  4. 背景颜色/图片由主题 CSS 中的 .page-head 类控制，
     可在 resources/sass/ 或 resources/css/ 中自定义。
================================================================================
--}}
<div class="page-head">
  <div class="container">
    <div class="page-title">{{ $title }}</div>
    <nav>
      <ol class="breadcrumb d-flex justify-content-center">
        <li class="breadcrumb-item"><a href="{{ front_route('home.index') }}"><i class="bi bi-house-door-fill"></i> 首页</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
      </ol>
    </nav>
  </div>
</div>