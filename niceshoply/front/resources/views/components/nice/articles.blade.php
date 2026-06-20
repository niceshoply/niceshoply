{{--
================================================================================
【文件说明】
  文章/博客列表展示模块组件（Articles Module）。
  在首页或自定义页面中以网格形式展示最新文章（博客/新闻），
  支持自定义标题、显示数量（limit）和每行列数（cols）。
  每篇文章通过 @include('shared.blog') 渲染卡片，展示封面图、标题、摘要等。

  在主题模板中通过 nice 标签调用：
    {nice:articles limit="3" cols="3" title="最新资讯"}
    {nice:articles limit="6" cols="2" catalog_id="2"}
  等价于 Blade 组件：
    <x-nice-articles limit="3" cols="3" title="最新资讯" />

【注册方式】
  FrontServiceProvider 中以别名 "nice-articles" 注册：
    Blade::component('nice-articles', Components\Nice\Articles::class);

【可用变量 / Props】
  以下 Props 通过标签属性传入组件类，组件类处理后注入视图：
  - $title       — 模块标题文字（字符串），为空时不显示标题区域
  - $limit       — 显示文章数量（整数，默认 3）
  - $cols        — 每行显示列数（整数，默认 3），Bootstrap 列宽：col-lg-{{ 12 / $cols }}
  - $catalog_id  — 按文章目录（分类）ID 筛选（可选，不传则显示所有目录的最新文章）
  - $articles    — 文章对象 Collection（由组件类自动查询并注入），每个对象包含：
                   id、title（多语言）、image、summary、published_at、url 等属性
                   以及 fallbackName()、fallbackSummary() 等多语言降级方法

  文章卡片子模板（shared/blog.blade.php）：
  - @include('shared.blog', ['item' => $article])
    $item 为当前文章对象，渲染封面图、标题、发布时间、摘要等信息

【插件钩子】
  本组件内部暂无 @hookinsert 点位。

【自定义建议】
  开发新主题时，可以：
  1. 修改 shared/blog.blade.php 来统一调整全站文章卡片样式。
  2. 在标题右侧添加「查看全部」链接：
     <a href="{{ front_route('articles.index') }}">查看全部</a>
  3. 若需展示某个目录的文章，设置 catalog_id 属性：
     {nice:articles catalog_id="1" limit="4" cols="4"}
  4. 文章发布、分类管理在后台「内容 → 文章」中操作，支持多语言内容。
  5. 封面图建议保持 16:9 或 3:2 的宽高比以保证视觉一致性，
     可通过 CSS object-fit: cover 在 shared/blog.blade.php 中实现。
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
      @foreach ($articles as $article)
        <div class="col-6 col-md-4 col-lg-{{ 12 / $cols }}">
          @include('shared.blog', ['item' => $article])
        </div>
      @endforeach
    </div>
  </div>
</section>
