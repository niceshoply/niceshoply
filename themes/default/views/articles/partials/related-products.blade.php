{{--
  ============================================================
  【文件说明】
    文章详情页正文下方 — 相关商品推荐局部视图。
    在文章正文结束后，展示与该文章关联的商品（网格布局，每行最多 3 列）。
    当关联商品数量 ≥ 6 时，显示「查看更多商品」按钮跳转至商品列表。
    若无关联商品则整块不渲染。

  【触发场景】
    由 articles/show.blade.php 通过 @include 引入，显示在正文内容区底部。

  【引入方式】
    @include('articles.partials.related-products', ['relatedProducts' => $relatedProducts])

  【可用变量】
    $relatedProducts — Collection  关联商品集合，每条通过 shared.product 局部视图渲染，
                         shared.product 期望的变量：$product（Product 模型）

  【辅助函数】
    front_route('products.index') — 生成带语言前缀的商品列表页 URL

  【局部视图依赖】
    @include('shared.product', ['product' => $product])
    — 通用商品卡片视图（含商品图、名称、价格、加购按钮等）

  【自定义建议】
    - 可调整网格列数（col-6 col-lg-4 → col-4 col-lg-3 等）
    - 可修改「≥ 6 件显示更多」的阈值
    - 可在标题处增加文章与商品关联的说明文字
    - 若需在文章列表页也展示相关商品，可在 shared/articles 中引入此视图
  ============================================================
--}}
@if($relatedProducts && $relatedProducts->count() > 0)
<div class="related-products-section mt-5">
  <h4 class="section-title mb-4">
    {{ __('front/article.related_products') }}
  </h4>
  <div class="row gx-3 gx-lg-4">
    @foreach($relatedProducts as $product)
      <div class="col-6 col-lg-4 mb-4">
        @include('shared.product', ['product' => $product])
      </div>
    @endforeach
  </div>
  @if($relatedProducts->count() >= 6)
    <div class="text-center mt-4">
      <a href="{{ front_route('products.index') }}" class="btn btn-primary btn-sm">
        {{ __('front/product.view_more_products') }}
      </a>
    </div>
  @endif
</div>
@endif