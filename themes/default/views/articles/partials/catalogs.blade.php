{{--
  ============================================================
  【文件说明】
    文章详情页侧边栏 — 文章分类列表局部视图。
    以无序列表形式展示所有文章分类，点击可跳转至对应分类的文章列表。

  【触发场景】
    由 articles/show.blade.php 通过 @include 引入，显示在侧边栏中。

  【引入方式】
    @include('articles.partials.catalogs', ['catalogs' => $catalogs])

  【可用变量】
    $catalogs — Collection  文章分类集合，每条含：
                  - translation->title  当前语言分类名称
                  - url                 分类过滤 URL（模型内置属性，带语言前缀）

  【自定义建议】
    - 可在列表项中加入文章数量统计（如 catalog->articles_count）
    - 可为当前激活分类添加 active 样式（与 request()->routeIs() 配合）
    - 可改为折叠式手风琴导航（适合分类较多的场景）
  ============================================================
--}}
<div class="mb-4">
  <div class="sidebar-title">{{ __('front/article.news_classification' )}}</div>
  <div class="sidebar-list">
    <ul>
      @foreach($catalogs as $catalog)
        <li><a href="{{ $catalog->url }}">{{ $catalog->translation->title ?? '' }}</a></li>
      @endforeach
    </ul>
  </div>
</div>