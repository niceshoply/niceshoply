{{--
  ============================================================
  【文件说明】
    用户中心 — 我的评价列表页。
    以表格形式展示当前会员提交过的所有商品评价，
    每条评价显示商品缩略图/名称、星级评分、评价内容摘要、评价日期，
    并提供删除操作。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）    ：account.reviews.index
    路由名称（DELETE）：account.reviews.destroy（用于删除指定评价）
    URL 示例：/{locale}/account/reviews
    控制器：Front\Account\ReviewController@index / @destroy

  【可用变量】
    $reviews — LengthAwarePaginator，评价记录分页集合，每条含：
                 id               评价 ID（用于删除路由参数）
                 content          评价文本内容
                 rating           评分（整数，1-5）
                 created_at       评价时间（Carbon）
                 product          关联商品对象：
                   product->translation->name  商品多语言名称
                   product->image_url          商品主图 URL
                   product->name               商品原始名称

  【Sections】
    body-class → 'page-review'
    content    → 评价表格 + 分页（无数据时显示 x-common-no-data 组件）
    footer     → 删除评价确认弹窗 JS（@push）

  【插件钩子】
    @hookinsert('account.review_index.top')    — 容器顶部
    @hookinsert('account.review_index.bottom') — 容器底部

  【子组件说明】
    <x-front-review :rating="$review['rating']"/> — 星级评分展示组件（传入 1-5 整数）

  【自定义建议】
    - 评价内容使用 sub_string($review->content, 12) 截断显示，可调整字符数
    - 删除使用 AJAX（axios.delete），删除成功后刷新页面
    - 若需展示评价图片，访问 $review->images（需确认模型是否包含此关联）
    - 分页：$reviews->links('console::vendor/pagination/bootstrap-4')，可替换为自定义分页视图
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 我的评价列表页。
    展示当前会员发表的所有商品评价，含商品缩略图、星级评分、评价内容摘要、
    日期，以及删除操作（AJAX 删除，需二次确认）。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.reviews.index
    路由名称（DELETE）：account.reviews.destroy/{id}（删除单条评价）
    URL 示例：/{locale}/account/reviews
    控制器：Front\Account\ReviewController@index / @destroy

  【可用变量】
    $reviews — LengthAwarePaginator，评价集合，每条含：
                 id                   评价 ID
                 rating               星级（1-5）
                 content              评价内容
                 created_at           评价时间（Carbon）
                 product->image_url   商品主图 URL
                 product->name        商品名（多语言对象需调 translation->name）

  【Sections】
    body-class → 'page-review'
    content    → 评价表格 + 分页
    footer     → 删除评价 JS（@push）

  【插件钩子】
    @hookinsert('account.review_index.top')    — 内容顶部
    @hookinsert('account.review_index.bottom') — 内容底部

  【前端交互】
    - .delete-review 按钮触发 layer.confirm 二次确认，确认后发送 AJAX DELETE 请求
    - x-front-review 组件渲染星级图标（:rating="$review['rating']"）

  【自定义建议】
    - sub_string($text, 12) 截断评价内容摘要，可调整长度参数
    - image_resize 用于缩略图裁剪，也可改用 CSS 来控制显示尺寸
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-review')

@section('content')
  <x-front-breadcrumb type="route" value="account.reviews.index" title="{{ __('front/account.reviews') }}"/>

  @hookinsert('account.review_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box review-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/review.review') }}</span>
          </div>

          @if ($reviews->count())
            <table class="table align-middle account-table-box table-response">
              <thead>
              <tr>
                <th>{{ __('console/review.product') }}</th>
                <th>{{ __('front/review.rating') }}</th>
                <th>{{ __('front/review.review_content') }}</th>
                <th>{{ __('front/common.date') }}</th>
                <th>{{ __('console/common.actions') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach($reviews as $review)
                <tr class="review-card-actions" data-id="{{ $review->id }}">
                  <td data-title="Product" data-bs-toggle="tooltip" title="{{ $review->product->translation->name }}">
                    <img src="{{ $review->product->image_url }}" alt="{{ $review->product->name }}" class="img-fluid wh-30">
                    {{ sub_string($review->product->translation->name, 24) }}
                  </td>
                  <td data-title="Rating"><x-front-review :rating="$review['rating']"/></td>
                  <td data-title="content" data-bs-toggle="tooltip"
                      title="{{ $review->content }}">{{ sub_string($review->content, 12)}}</td>
                  <td data-title="Date">{{ $review->created_at->format('Y-m-d') }}</td>
                  <td data-title="Actions">
                    <button type="button" class="btn delete-review btn-sm btn-outline-danger"
                            data-url="{{ account_route('reviews.destroy', $review->id) }}">{{ __('front/common.delete') }}</button>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>

            {{ $reviews->links('console::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.review_index.bottom')

@endsection

@push('footer')
  <script>
    $('.delete-review').on('click', function () {
      const url = $(this).data('url');
      layer.confirm('{{ __('front/common.delete_confirm') }}', {
        btn: ['{{ __('front/common.confirm') }}', '{{ __('front/common.cancel') }}']
      }, function () {
        axios.delete(url).then(function (res) {
          if (res.success) {
            layer.msg(res.message, {icon: 1, time: 1000}, function () {
              window.location.reload()
            });
          }
        })
      });
    });
  </script>
@endpush
