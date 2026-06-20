{{--
===========================================================================
【文件说明】
  品牌介绍区域局部模板，在商品列表上方展示品牌的 Logo、名称、描述和附加信息徽章。
  仅当 $brand 存在时才渲染（条件: @if($brand)），布局与分类 _intro 组件结构一致。
  由 brands/show.blade.php 通过 @include('brands.partials._intro') 引入。

【来源视图】
  brands/show.blade.php

【传入变量】
  $brand  — Brand 模型  当前品牌对象（由父视图传递）
    $brand->logo        : string|null  品牌 Logo 图片路径（为 null 时传空字符串给 image_resize）
    $brand->name        : string       品牌名称
    $brand->description : string|null  品牌富文本描述（使用 {!! !!} 输出支持 HTML）
    $brand->first       : string|null  品牌名首字母（如 'A', 'B'...）

【辅助函数】
  image_resize($brand->logo ?? '', 200, 200) — 生成 200×200 品牌 Logo URL
                                               logo 为 null 时传入空字符串

【关键 CSS 类说明】
  .brand-intro-section     — 品牌介绍区块外层容器
  .brand-image-wrapper     — Logo 图片容器（含 .image-overlay 遮罩）
  .brand-hero-image        — 品牌 Logo 图片（200×200，圆角阴影）
  .brand-title             — 品牌名称（h1，Bootstrap display-6）
  .brand-summary           — 品牌描述文本区（支持富文本 HTML 输出）
  .brand-meta              — 品牌附加信息容器（flex 横向排列徽章）
  .badge.bg-light.text-dark — 首字母徽章
  .badge.bg-primary        — 品牌商品徽章（固定文案，无商品数量）

【翻译键】
  front/brand.first_letter   — "首字母" 或 "First Letter" 等本地化文本
  front/brand.brand_products — "品牌商品" 或 "Brand Products" 等本地化文本

【Sections / Blocks】
  无

【插件钩子】
  无

【自定义建议】
  - 显示品牌商品数量：将固定的 badge 替换为动态值，前提是控制器预加载了 withCount('products')：
    {{ $brand->products_count }} {{ __('front/common.products') }}
  - 修改 Logo 尺寸：调整 image_resize() 参数，同步修改 CSS 中 .brand-hero-image 的宽高。
  - 若品牌无描述：当前 @if($brand->description ?? false) 会静默跳过，
    可替换为提示文案 @else <p class="text-muted">暂无品牌描述</p>。
  - 将 .image-overlay 遮罩样式定义到主题 CSS，而非仅依赖父组件的 CSS 定义。
===========================================================================
--}}
{{-- Brand intro section (before product list) --}}
@if($brand)
  <div class="brand-intro-section mb-4">
    <div class="card border-0 shadow-sm bg-gradient">
      <div class="card-body p-4">
        <div class="row align-items-center">
          <div class="col-md-3 text-center mb-3 mb-md-0">
            <div class="brand-image-wrapper position-relative">
              <img src="{{ image_resize($brand->logo ?? '', 200, 200) }}" 
                   alt="{{ $brand->name }}" 
                   class="img-fluid rounded-3 shadow brand-hero-image">
              <div class="image-overlay"></div>
            </div>
          </div>
          <div class="col-md-9">
            <div class="brand-content-wrapper">
              <h1 class="brand-title display-6 fw-bold mb-3 text-primary">{{ $brand->name }}</h1>
              
              {{-- Brand description or summary if available --}}
              @if($brand->description ?? false)
                <div class="brand-summary mb-4">
                  <div class="summary-content text-muted fs-5 lh-lg">
                    {!! $brand->description !!}
                  </div>
                </div>
              @endif

              {{-- Brand additional info --}}
              <div class="brand-meta d-flex flex-wrap gap-3">
                @if($brand->first)
                  <span class="badge bg-light text-dark fs-6">
                    <i class="bi bi-tag-fill me-1"></i>{{ __('front/brand.first_letter') }}: {{ $brand->first }}
                  </span>
                @endif
                
                <span class="badge bg-primary fs-6">
                  <i class="bi bi-shop me-1"></i>{{ __('front/brand.brand_products') }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif