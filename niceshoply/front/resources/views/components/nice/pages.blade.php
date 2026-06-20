{{--
================================================================================
【文件说明】
  自定义单页面列表展示模块组件（Pages Module）。
  在首页或自定义页面中以网格形式展示「单页面」（Page）列表，
  通常用于展示品牌故事、服务介绍、政策说明等静态页面入口。
  每个 Page 显示封面图（可选）和页面标题，点击跳转到对应单页面详情。

  在主题模板中通过 nice 标签调用：
    {nice:pages limit="4" cols="4" title="关于我们"}
    {nice:pages limit="6" cols="3"}
  等价于 Blade 组件：
    <x-nice-pages limit="4" cols="4" title="关于我们" />

【注册方式】
  FrontServiceProvider 中以别名 "nice-pages" 注册：
    Blade::component('nice-pages', Components\Nice\Pages::class);

【可用变量 / Props】
  以下 Props 通过标签属性传入组件类，组件类处理后注入视图：
  - $title   — 模块标题文字（字符串），为空时不显示标题区域
  - $limit   — 显示页面数量（整数，默认 4）
  - $cols    — 每行显示列数（整数，默认 4），Bootstrap 列宽：col-lg-{{ 12 / $cols }}
  - $pages   — 单页面对象 Collection（由组件类自动查询并注入），每个对象包含：
      url              — 页面详情链接（通过 url 属性或路由生成）
      image            — 封面图路径（可为 null，为 null 时不渲染 <img> 标签）
      fallbackName()   — 方法，返回当前语言的页面标题（自动降级到默认语言）

  全局辅助函数：
  - image_origin($path)  — 将相对路径转为完整图片 URL

【插件钩子】
  本组件内部暂无 @hookinsert 点位。

【自定义建议】
  开发新主题时，可以：
  1. 为无图片的页面卡片添加默认占位图或纯色背景块：
     @if($page->image ?? false)
       <img ...>
     @else
       <div class="page-placeholder-img bg-light rounded mb-2"></div>
     @endif
  2. 在页面标题下方添加 fallbackDescription() 摘要文字（如果模型支持）：
     <div class="text-muted small">{{ $page->fallbackDescription() }}</div>
  3. 单页面在后台「内容 → 单页面」中管理，支持多语言标题和富文本内容。
  4. 若需按 group 或 tag 筛选显示特定类型的页面，可扩展组件类的查询条件。
  5. $cols 建议使用 12 的因数（2、3、4、6），避免列宽计算出现小数。
================================================================================
--}}
<section class="module-line">
  <div class="container">
    @if($title)
      <div class="module-title-wrap">
        <div class="module-title">{{ $title }}</div>
      </div>
    @endif
    <div class="row gx-3 gx-lg-4">
      @foreach ($pages as $page)
        <div class="col-6 col-md-4 col-lg-{{ 12 / $cols }}">
          <a href="{{ $page->url ?? '#' }}" class="d-block text-decoration-none mb-3">
            @if($page->image ?? false)
              <img src="{{ image_origin($page->image) }}" alt="{{ $page->fallbackName() }}" class="img-fluid rounded mb-2">
            @endif
            <div class="fw-medium text-dark">{{ $page->fallbackName() }}</div>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>
