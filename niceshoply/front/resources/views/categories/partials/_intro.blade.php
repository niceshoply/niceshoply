{{--
===========================================================================
【文件说明】
  分类简介区域局部模板，在商品列表上方展示分类的封面图和摘要（summary）。
  仅当分类存在 summary 翻译内容时才渲染，否则不输出任何 HTML。
  由 categories/show.blade.php 通过 @include('categories.partials._intro') 引入。

【来源视图】
  categories/show.blade.php

【传入变量】
  $category  — Category 模型  当前分类对象（由父视图传递）
    $category->fallbackName('summary')  : string|null  分类摘要（多语言 fallback）
    $category->fallbackName('name')     : string       分类名称（多语言 fallback）
    $category->image                    : string|null  分类图片路径

【辅助函数】
  image_resize($path, 200, 200) — 生成 200×200 分类封面缩略图 URL
    当 $category->image 为 null/空时传入空字符串，image_resize 应能安全处理

【关键 CSS 类说明】
  .category-intro-section   — 简介区块外层容器（Bootstrap card）
  .category-image-wrapper   — 图片容器（含 .image-overlay 遮罩层）
  .category-hero-image      — 分类封面图（200×200，圆角阴影）
  .category-title           — 分类名称标题（h1，Bootstrap display-6）
  .category-summary         — 摘要内容区（支持 HTML 富文本）
  .summary-content          — 摘要文本（text-muted，使用 {!! !!} 不转义输出）

【Sections / Blocks】
  无（此组件无独立的 section 或 @push）

【插件钩子】
  无

【自定义建议】
  - 修改图片尺寸：调整 image_resize() 参数（当前 200×200），同步修改 CSS 尺寸。
  - 隐藏此区块：在 categories/show.blade.php 中移除对应 @include 行，
    或将条件改为 @if(false) 临时禁用。
  - 修改卡片背景：调整 .bg-gradient 类或添加内联 style 设置背景色/图。
  - 如需同时展示商品数量：在摘要下方添加 $category->products_count 相关的 badge 输出。
===========================================================================
--}}
{{-- Category intro section (before product list) --}}
@if($category->fallbackName('summary'))
  <div class="category-intro-section mb-4">
    <div class="card border-0 shadow-sm bg-gradient">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-md-3 text-center mb-3 mb-md-0">
            <div class="category-image-wrapper position-relative">
              <img src="{{ image_resize($category->image ?? '', 200, 200) }}" 
                   alt="{{ $category->fallbackName('name') }}" 
                   class="img-fluid rounded-3 shadow category-hero-image">
              <div class="image-overlay"></div>
            </div>
          </div>
          <div class="col-md-9">
            <div class="category-content-wrapper">
              <h1 class="category-title display-6 fw-bold mb-3 text-primary">{{ $category->fallbackName('name') }}</h1>
              
              {{-- Category summary --}}
              @if($category->fallbackName('summary'))
                <div class="category-summary mb-4">
                  <div class="summary-content text-muted fs-5 lh-lg">
                    {!! $category->fallbackName('summary') !!}
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif