{{--
===========================================================================
【文件说明】
  商品评价区域容器组件，集成：
    1. 评价提交入口（根据登录状态、是否已评价、是否需购买后评价动态展示）
    2. 评价列表（分页加载，通过 Ajax "加载更多"追加）
  由 products/show.blade.php 通过 @include 在评价 Tab 面板中引入。

【来源视图】
  products/show.blade.php（评价 Tab #product-review 面板内）

【可用变量】（继承自父视图 + 控制器注入）
  $product    — Product 模型     商品对象
    $product->id : int  用于构建加载更多评价的 API 路由和 data-product-id
  $reviews    — LengthAwarePaginator  初始评价分页集合（第 1 页）
    $reviews->hasMorePages() : bool  是否有更多页，决定是否显示"加载更多"按钮
  $reviewed   — bool              当前用户是否已对本商品提交过评价

【全局辅助函数】
  current_customer()    — 返回当前已登录的前台用户对象，未登录返回 null
  system_setting(key)   — 读取系统设置；'bought_review' 为 true 时表示需购买后才能评价
  front_route(name, params) — 生成带语言前缀的路由 URL
    front_route('products.reviews', ['product' => $product->id]) — 获取评价列表 API URL
  account_route(name)   — 生成用户中心路由 URL（如 orders.index 指向订单列表）

【评价显示逻辑】
  ┌─ 已登录
  │  ├─ 系统设置：无需购买即可评价（!bought_review）
  │  │  ├─ 未评价 → 显示评价表单（@include('shared.review')）
  │  │  └─ 已评价 → 显示"已评价"状态按钮
  │  └─ 需购买后评价（bought_review=true）
  │     └─ 显示"去订单页面评价"链接按钮
  └─ 未登录 → 显示"请先登录"按钮（点击触发 inno.openLogin()）

【包含的局部模板】
  shared.review                    — 评价提交表单（星级评分 + 内容输入）
  products.components._review_list — 评价列表渲染（传入 $reviews 数据）

【Sections / Blocks】
  footer — 追加（@push）"加载更多评价"的 Ajax 逻辑

【Ajax 接口说明】
  GET front_route('products.reviews', ['product' => $product->id])
  参数: page=N（页码）
  响应结构:
    { success: bool, data: { html: '评价列表 HTML', has_more: bool } }
  成功后追加 html 到 .review-list-container，has_more=false 时移除按钮

【插件钩子】
  无

【自定义建议】
  - 修改评价提交表单样式：编辑 shared/review.blade.php。
  - 修改每次加载的评价数量：在控制器 products.reviews 方法中调整分页数。
  - 分页方式改为无限滚动：将"加载更多"按钮替换为 IntersectionObserver 触发的自动加载逻辑。
  - 评价列表追加位置：当前追加到 .review-list-container 末尾，如需覆盖可改为 .html()。
===========================================================================
--}}
@if (current_customer() && !system_setting('bought_review'))

  @if (!$reviewed)
    @include('shared.review')
  @else
    <div class="m-5 text-center">
      <button class="btn btn-primary">{{ __('front/product.have_reviewed') }}</button>
    </div>
  @endif
@else
  <div class="m-5 text-center">
    @if (!current_customer())
      <a class="btn btn-primary" href="javascript:inno.openLogin()">{{ __('front/product.please_login_first') }}</a>
    @else
      <a class="btn btn-primary" href="{{ account_route('orders.index') }}"
         target="_blank">{{ __('front/product.visit_order_to_review') }}</a>
    @endif
  </div>
@endif

<div class="review-list-container">
  @include('products.components._review_list', ['reviews' => $reviews])
</div>

@if ($reviews->hasMorePages())
  <div class="text-center mt-3">
    <button class="btn btn-outline-primary load-more-reviews" data-page="2" data-product-id="{{ $product->id }}">
      {{ __('front/common.load_more') }}
    </button>
  </div>
@endif

@push('footer')
  <script>
    $(document).ready(function () {
      $('.load-more-reviews').on('click', function () {
        const button = $(this);
        const page = button.data('page');

        button.prop('disabled', true).html(
          '<i class="bi bi-arrow-repeat spin"></i> {{ __('front/common.loading') }}');

        axios.get(`{{ front_route('products.reviews', ['product' => $product->id]) }}`, {
          params: {
            page: page
          }
        }).then(function (response) {
          if (response.success) {
            $('.review-list-container').append(response.data.html);

            if (response.data.has_more) {
              button.data('page', page + 1).prop('disabled', false).text(
                '{{ __('front/product.load_more') }}');
            } else {
              button.remove();
            }
          }
        }).catch(function (error) {
          console.error('Failed to load reviews:', error);
          button.prop('disabled', false).text('{{ __('front/product.load_more') }}');
          inno.msg('{{ __('front/product.load_failed') }}');
        });
      });
    });
  </script>
@endpush
