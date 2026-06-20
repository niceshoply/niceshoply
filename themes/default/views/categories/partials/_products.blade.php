{{--
===========================================================================
【文件说明】
  分类商品列表区域局部模板，负责将商品以网格或列表形式渲染并输出分页导航。
  显示模式由 URL 参数 style_list 决定（grid=网格，list=列表）。
  由 categories/show.blade.php 通过 @include('categories.partials._products') 引入。

【来源视图】
  categories/show.blade.php

【传入变量】
  $products  — LengthAwarePaginator  商品分页集合（Product 模型）
    每项 $product 传入 shared.product 组件渲染，常用属性:
      id, name（多语言）, price（原始数值）, image_url, url
      masterSku->price_format（格式化后的价格字符串）

【URL 查询参数（request()）】
  style_list — 视图模式: 'grid'（默认）或 'list'
               影响商品行的 CSS 类和商品单项的列宽

【关键 CSS 类说明】
  .row.gx-3.gx-lg-4        — 商品网格容器（水平间距）
  .product-list-wrap        — 列表模式专属样式类（style_list=list 时追加）
  .col-6.col-md-4           — 网格模式：移动端2列，平板3列
  .col-12                   — 列表模式：单行铺满

【包含的局部模板】
  shared.product  — 单个商品卡片，自动适应 网格/列表 两种布局

【分页说明】
  $products->onEachSide(1)->links('console::vendor/pagination/bootstrap-4')
    onEachSide(1) — 当前页两侧各显示 1 个页码
    使用 console 包内置的 Bootstrap 4 分页视图

【Sections / Blocks】
  无

【插件钩子】
  无

【自定义建议】
  - 修改网格列数：调整 col-6 col-md-4 的栅格类（如改为 col-6 col-md-3 实现4列网格）。
  - 自定义分页样式：将分页模板路径替换为主题自定义的分页视图。
  - 如需商品数量为0时显示空状态：在 @foreach 外层添加 @if($products->count()) ... @else 空状态 @endif。
  - 无限滚动：移除分页导航，改用 IntersectionObserver 监听页面底部，Ajax 加载下一页商品追加至 .row。
===========================================================================
--}}
{{-- 产品列表区域 --}}
<div class="row gx-3 gx-lg-4 {{ request('style_list') == 'list' ? 'product-list-wrap' : ''}}">
  @foreach ($products as $product)
    <div class="{{ !request('style_list') || request('style_list') == 'grid' ? 'col-6 col-md-4' : 'col-12'}}">
      @include('shared.product')
    </div>
  @endforeach
</div>

{{-- 分页导航 --}}
{{ $products->onEachSide(1)->links('console::vendor/pagination/bootstrap-4') }}