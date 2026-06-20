{{--
===========================================================================
【文件说明】
  套装商品子项展示组件。仅当商品类型为 'bundle'（套装）且存在子项时才渲染，
  以横向排列形式展示套装内包含的子商品缩略图、名称及数量。
  由 products/show.blade.php 通过 @include('products.components._bundle_items') 引入。

【来源视图】
  products/show.blade.php

【可用变量】（继承自父视图 + 控制器注入）
  $product        — Product 模型     商品对象
    $product->type : string  商品类型，只有值为 'bundle' 时本组件才展示内容
  $bundle_items   — Collection        套装子项集合（BundleItem 模型集合）
    $bundleItem->sku            : Sku 模型  子商品的 SKU
      $bundleItem->sku->getImageUrl(40, 40) : string  生成 40×40 图片 URL
      $bundleItem->sku->full_name           : string  子商品完整名称（含规格名）
    $bundleItem->quantity       : int    该子商品在套装中的数量

【辅助函数】
  sub_string($str, $len) — 截断字符串到指定长度（用于避免名称过长破坏布局）
                           当前截断长度为 68 字符

【关键 CSS 类说明】
  .bundle-items-display  — 套装展示区块外层容器
  .bundle-title          — 套装标题（"套装包含："）
  .bundle-products       — 子商品横向排列容器（flexbox，支持换行）
  .bundle-separator      — 子商品之间的分隔符（"+"号）
  .bundle-product-item   — 单个子商品容器（图片 + 信息）
  .bundle-product-image  — 子商品缩略图（40×40，object-fit: cover）
  .bundle-product-name   — 子商品名称（有 tooltip 显示完整名称）

【Sections / Blocks】
  无（此组件无独立的 section 或 @push 块）

【插件钩子】
  无

【自定义建议】
  - 修改子商品图片尺寸：调整 getImageUrl(40, 40) 的宽高参数（同步调整 CSS width/height）。
  - 修改名称截断长度：调整 sub_string() 的第二个参数（当前 68）。
  - 如需链接到子商品详情页：在 .bundle-product-item 外层包裹 <a href="{{ $bundleItem->sku->product->url }}">。
  - tooltip 效果依赖 Bootstrap 5 的 Tooltip 组件，需在全局 JS 中初始化：
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el))
===========================================================================
--}}
@if($product->type === 'bundle' && $bundle_items->count() > 0)
  <div class="bundle-items-display mb-3">
    <h6 class="bundle-title">{{ __('front/product.bundle_includes') }}:</h6>
    <div class="bundle-products d-flex align-items-center flex-wrap">
      @foreach($bundle_items as $index => $bundleItem)
        @if($index > 0)
          <span class="bundle-separator mx-2">+</span>
        @endif
        <div class="bundle-product-item d-flex align-items-center">
          <img src="{{ $bundleItem->sku->getImageUrl(40, 40) }}"
               alt="{{ $bundleItem->sku->full_name }}"
               class="bundle-product-image me-2"
               style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
          <div class="bundle-product-info">
            <div class="bundle-product-name" data-bs-toggle="tooltip" title="{{ $bundleItem->sku->full_name }}">
              {{ sub_string($bundleItem->sku->full_name, 68) }}
            </div>
            @if($bundleItem->quantity > 0)
              <small class="text-muted">× {{ $bundleItem->quantity }}</small>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endif 