{{--
  ============================================================
  【文件说明】
    用户中心 — 我的收藏（心愿单）页。
    展示当前会员收藏的所有商品，支持直接加入购物车或取消收藏。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.favorites.index
    URL 示例：/{locale}/account/favorites
    控制器：Front\Account\FavoriteController@index

  【可用变量】
    $favorites — LengthAwarePaginator，收藏记录集合，每条含关联的 $item->product：
                   product->id              商品 ID
                   product->image_url       商品主图 URL
                   product->url             商品详情页 URL
                   product->translation->name  当前语言商品名
                   product->masterSku->id      默认 SKU ID（用于加购）
                   product->masterSku->price_format        格式化价格
                   product->masterSku->origin_price_format 格式化原价（若有）

  【Sections】
    body-class → 'page-wishlist'
    content    → 收藏商品网格列表
    footer     → 取消收藏 JS（@push）

  【插件钩子】
    @hookinsert('account.favorites.top')    — 容器顶部
    @hookinsert('account.favorites.bottom') — 容器底部

  【前端交互】
    - .cancel-favorite 按钮触发 inno.addWishlist(id, 1, null, callback)，
      成功后 800ms 刷新页面以更新收藏列表
    - .btn-add-cart 按钮由 common.js 中的全局加购逻辑处理

  【自定义建议】
    - 如需显示分页，在循环后追加 $favorites->links()
    - 收藏卡片样式 .product-grid-item 在 product-item.scss 中定义，可自定义
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 我的收藏（心愿单）页。
    以商品卡片网格形式展示当前会员收藏的所有商品，
    每个商品卡片显示图片、名称、价格，并提供"加入购物车"和"取消收藏"操作。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.favorites.index
    URL 示例：/{locale}/account/favorites
    控制器：Front\Account\FavoriteController@index

  【可用变量】
    $favorites — Collection，收藏记录集合，每条含 product 关联对象：
                   $product->id                   商品 ID
                   $product->url                  商品详情页 URL
                   $product->image_url            商品主图 URL
                   $product->translation->name    商品名称（多语言）
                   $product->masterSku->id        主 SKU 的 ID
                   $product->masterSku->price_format        销售价（格式化）
                   $product->masterSku->origin_price        原价（可为空）
                   $product->masterSku->origin_price_format 原价（格式化）

  【Sections】
    body-class → 'page-wishlist'
    content    → 商品卡片网格（空时显示 x-common-no-data 组件）
    footer     → 取消收藏点击事件 JS（@push）

  【插件钩子】
    @hookinsert('account.favorites.top')    — 容器顶部
    @hookinsert('account.favorites.bottom') — 容器底部

  【自定义建议】
    - 取消收藏通过 inno.addWishlist(id, 1, null, callback) 实现，
      第二个参数 1 表示从收藏中移除，成功后自动刷新页面
    - "加入购物车"按钮（.btn-add-cart）依赖全局 inno.addCart 逻辑，
      需在主题 JS 中确保该事件已绑定
    - 可在卡片底部添加"立即购买"按钮，跳转至 $product->url
    - 若要显示商品库存状态（是否售罄），访问 $product->masterSku->stock
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-wishlist')

@section('content')
<x-front-breadcrumb type="route" value="account.favorites.index" title="{{ __('front/account.favorites') }}" />

@hookinsert('account.favorites.top')

<div class="container">
  <div class="row">
    <div class="col-12 col-lg-3">
      @include('shared.account-sidebar')
    </div>
    <div class="col-12 col-lg-9">
      <div class="account-card-box wishlist-box">
        <div class="account-card-title d-flex justify-content-between align-items-center">
          <span class="fw-bold">{{ __('front/favorites.favorites') }}</span>
        </div>

        @if ($favorites->count())
          <div class="row">
            @foreach ($favorites as $product)
            @php($product = $product->product)
            <div class="col-6 col-md-3">
              <div class="product-grid-item">
                <div class="image">
                  <div class="cancel-favorite" data-id="{{ $product->id }}" data-in-wishlist="1"><i class="bi bi-trash"></i></div>
                  <a href="{{ $product->url }}">
                    <img src="{{ $product->image_url }}" class="img-fluid">
                  </a>
                </div>
                <div class="product-item-info">
                  <div class="product-name"><a href="{{ $product->url }}">{{ $product->translation->name }}</a></div>
                  <div class="product-bottom">
                    <div class="product-bottom-btns">
                      <div class="btn-add-cart cursor-pointer" data-id="{{ $product->id }}"
                        data-sku-id="{{ $product->masterSku->id }}">{{ __('front/product.add_to_cart') }}</div>
                    </div>
                    <div class="product-price">
                      @if ($product->masterSku->origin_price)
                      <div class="price-old">{{ $product->masterSku->origin_price_format }}</div>
                      @endif
                      <div class="price-new">{{ $product->masterSku->price_format }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        @else
          <x-common-no-data />
        @endif
      </div>
    </div>
  </div>
</div>

@hookinsert('account.favorites.bottom')

@endsection

@push('footer')
<script>
  $('.cancel-favorite').on('click', function () {
    const id = $(this).attr('data-id');
    inno.addWishlist(id, 1, null, function () {
      setTimeout(() => {
        location.reload();
      }, 800);
    })
  });
</script>
@endpush