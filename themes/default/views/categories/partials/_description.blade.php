{{--
===========================================================================
【文件说明】
  分类底部详细描述区域局部模板，展示分类的富文本 content 字段内容（通常为
  SEO 长描述文章）。仅当分类存在 content 翻译时才渲染，位于商品列表下方。
  由 categories/show.blade.php 通过 @include('categories.partials._description') 引入。

【来源视图】
  categories/show.blade.php

【传入变量】
  $category  — Category 模型
    $category->fallbackName('content') : string|null  分类详细描述（富文本 HTML）
                                         多语言 fallback：优先当前语言，无则取后台默认语言

【关键 CSS 类说明】
  .mt-5                  — 与上方商品列表保持间距
  .card                  — Bootstrap 卡片容器（无边框，有阴影）
  .card-header.bg-primary — 蓝色标题栏（含图标 bi-file-text 和翻译标题）
  .card-body             — 内容区（{!! !!} 不转义输出，支持富文本）

【辅助函数】
  __('front/category.content') — 分类 content 标签的翻译文本（语言文件键）

【Sections / Blocks】
  无

【插件钩子】
  无

【自定义建议】
  - 修改卡片样式：调整 .card-header 的背景色类（如改为 bg-secondary 或自定义色）。
  - 若需折叠/展开：为 .card-body 添加 collapse 逻辑和展开按钮。
  - content 字段支持 HTML，主题 CSS 中应为 .card-body 的富文本子元素（h1-h6, ul, ol, img）
    提供合理的排版样式，避免与站点样式冲突。
  - SEO 注意：此区域对搜索引擎友好，建议在后台为每个分类填写包含目标关键词的描述。
===========================================================================
--}}
{{-- 分类详细描述区域 --}}
@if($category->fallbackName('content'))
<div class="mt-5">
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white py-3">
      <h5 class="card-title mb-0 fw-bold">
        <i class="bi bi-file-text me-2"></i>
        {{ __('front/category.content') }}
      </h5>
    </div>
    <div class="card-body p-4">
      <div class="text-muted lh-lg">
        {!! $category->fallbackName('content') !!}
      </div>
    </div>
  </div>
</div>
@endif