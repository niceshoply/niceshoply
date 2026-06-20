{{--
===========================================================================
【文件说明】
  商品详情页，展示单个商品的完整信息：图片、标题、价格、库存、规格选择、
  自定义选项、套装子商品、商品描述/参数属性、评价、相关商品。

【对应路由 / 控制器】
  路由名称  : products.show
  URL 示例  : /products/{slug}  或  /{lang}/products/{slug}
  控制器    : App\Http\Controllers\Front\ProductController@show

【可用变量】
  $product        — Product 模型     商品对象
                    常用属性:
                      id, fallbackName('name'/'summary'/'selling_point'/'content')
                      price, image_url, images（数组）, video（数组）
                      categories（关联集合）, brand（关联模型）
                      masterSku（主 SKU）, hasFavorite()
  $sku            — array             当前激活 SKU 的数组快照
                    键: id, code, model, price, price_format,
                        origin_price, origin_price_format, quantity, variants[]
  $skus           — array             商品全部 SKU 列表（规格切换用）
  $variants       — array             规格维度数组，结构:
                    [['name'=>['zh'=>'颜色',...], 'values'=>[['name'=>...,'image'=>...],...]],...]
  $attributes     — array|null        商品参数属性分组，结构:
                    [['attribute_group_name'=>'...', 'attributes'=>[['attribute'=>'...','attribute_value'=>'...'],...]],...]
  $productOptions — Collection|null   商品自定义选项集合（加购时的附加选择，如刻字、礼品包装等）
  $bundle_items   — Collection        套装（bundle）商品子项集合
  $related        — Collection        相关推荐商品集合
  $reviews        — LengthAwarePaginator  商品评价分页集合
  $reviewed       — bool              当前用户是否已评价此商品

【Sections / Blocks】
  body-class  — 值为 'page-product'
  title       — SEO 标题（由 MetaInfo 自动填充）
  description — SEO 描述
  keywords    — SEO 关键词
  content     — 页面主体内容
  header      — head 区域（注入 Swiper / PhotoSwipe / Video.js 资源）
  footer      — 页脚 JS（数量加减、加购/立即购买逻辑）

【包含的局部模板】
  products.components._images         — 商品图片区（缩略图 + 主图 + 移动端轮播）
  products.components._bundle_items   — 套装子商品展示
  products.components._variants       — 规格选择器
  products.components._options        — 自定义选项选择器
  products.components._review_section — 评价提交 + 评价列表容器
  shared.product                      — 相关商品卡片（复用公共组件）

【插件钩子】
  @hookinsert('product.show.top')               — 详情页顶部
  @hookinsert('product.detail.stock.after')     — 库存状态下方
  @hookinsert('product.detail.brand.after')     — 品牌信息下方
  @hookinsert('product.detail.cart.after')      — 购物车按钮旁（可注入自定义按钮）
  @hookinsert('product.detail.after')           — 加入收藏按钮后
  @hookinsert('product.detail.description.after') — 商品描述 Tab 内容末尾
  @hookinsert('product.detail.tab.link.after')  — Tab 导航链接末尾（可追加新 Tab）
  @hookinsert('product.detail.tab.pane.after')  — Tab 内容面板末尾（可追加新面板）
  @hookinsert('product.show.bottom')            — 详情页底部
  @hookupdate('front.product.show.price')       — 价格区块（可整体替换价格展示逻辑）

【自定义建议】
  - 修改加购逻辑：调整 @push('footer') 中 .add-cart/.buy-now 的 click 处理。
  - 新增 Tab：在 @hookinsert('product.detail.tab.link.after') 和
    @hookinsert('product.detail.tab.pane.after') 注入对应的 <li> 和 <div class="tab-pane">。
  - 隐藏在线下单功能：修改系统设置 disable_online_order=true，或自定义判断逻辑。
  - 规格切换后的图片更新逻辑在 _variants.blade.php 的脚本中处理。
===========================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-product')

@section('title', \NiceShoply\Common\Libraries\MetaInfo::getInstance($product)->getTitle())
@section('description', \NiceShoply\Common\Libraries\MetaInfo::getInstance($product)->getDescription())
@section('keywords', \NiceShoply\Common\Libraries\MetaInfo::getInstance($product)->getKeywords())
@section('canonical', \NiceShoply\Common\Libraries\MetaInfo::getInstance($product)->getCanonical(front_route('products.show', ['product' => $product->id])))

@push('json_ld')
@php
  $jsonLd = \NiceShoply\Common\Services\Seo\JsonLdService::getInstance();
  $breadcrumbs = [
    ['name' => __('front/common.home'), 'url' => front_route('home.index')],
    ['name' => \NiceShoply\Common\Libraries\MetaInfo::getInstance($product)->getTitle()],
  ];
@endphp
{!! $jsonLd->renderMultiple([
  $jsonLd->organization(),
  $jsonLd->product($product),
  $jsonLd->breadcrumb($breadcrumbs),
]) !!}
@endpush

@push('header')
  <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">

  <script src="{{ asset('vendor/photoswipe/umd/photoswipe.umd.min.js') }}"></script>
  <script src="{{ asset('vendor/photoswipe/umd/photoswipe-lightbox.umd.min.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('vendor/photoswipe/photoswipe.css') }}">
  
  <script src="{{ asset('vendor/video-js/video.min.js') }}"></script>
  <link href="{{ asset('vendor/video-js/video-js.css') }}" rel="stylesheet">
  
@endpush

@section('content')

  <x-front-breadcrumb type="product" :value="$product"/>

  @hookinsert('product.show.top')

  <div class="container">
    <div class="page-product-top">
      <div class="row">
        <div class="col-12 col-lg-6 product-left-col">
          <div class="product-images">
            @include('products.components._images')
          </div>
        </div>

        <div class="col-12 col-lg-6">
          <div class="product-info">
            <h1 class="product-title">{{ $product->fallbackName() }}</h1>
            @hookupdate('front.product.show.price')
            <div class="product-price">
              <span class="price">{{ $sku['price_format'] }}</span>
              @if($sku['origin_price'])
                <span class="old-price ms-2">{{ $sku['origin_price_format'] }}</span>
              @endif
            </div>
            @endhookupdate

            <div class="stock-wrap">
              @if($sku['quantity'] > 0)
                <div class="in-stock badge">{{ __('front/product.in_stock') }}</div>
              @else
                <div class="out-stock badge d-none">{{ __('front/product.out_stock') }}</div>
              @endif
            </div>

            @hookinsert('product.detail.stock.after')

            <div class="sub-product-title">{{ $product->fallbackName('summary') }}</div>

            @include('products.components._bundle_items')

            <ul class="product-param">
              <li class="sku"><span class="title">{{ __('front/product.sku_code') }}:</span> <span
                  class="value">{{ $sku['code'] }}</span></li>
              <li class="model {{ !($sku['model'] ?? false) ? 'd-none' : '' }}"><span class="title">{{ __('front/product.model') }}:</span>
                <span class="value">{{ $sku['model'] }}</span></li>
              @if ($product->categories->count())
                <li class="category">
                  <span class="title">{{ __('front/product.category') }}:</span>
                  <span class="value">
                @foreach ($product->categories as $category)
                      <a href="{{ $category->url }}"
                         class="text-dark">{{ $category->fallbackName() }}</a>{{ !$loop->last ? ', ' : '' }}
                    @endforeach
              </span>
                </li>
              @endif
              @if($product->brand)
                <li class="brand">
                  <span class="title">{{ __('front/product.brand') }}:</span> <span class="value">
                <a href="{{ $product->brand->url }}"> {{ $product->brand->name }} </a>
              </span>
                </li>
              @endif
              @hookinsert('product.detail.brand.after')
            </ul>

            @include('products.components._variants')
            
            @include('products.components._options')

            @if(!system_setting('disable_online_order'))
              <div class="product-info-bottom">
                <div class="quantity-wrap">
                  <div class="minus"><i class="bi bi-dash-lg"></i></div>
                  <input type="number" class="form-control product-quantity" value="1"
                         data-sku-id="{{ $sku['id'] }}">
                  <div class="plus"><i class="bi bi-plus-lg"></i></div>
                </div>
                <div class="product-info-btns">
                  <button class="btn btn-primary add-cart" data-id="{{ $product->id }}"
                          data-price="{{ $product->masterSku->price }}">
                    {{ __('front/product.add_to_cart') }}
                  </button>
                  <button class="btn buy-now ms-2" data-id="{{ $product->id }}"
                          data-price="{{ $product->masterSku->price }}">
                    {{ __('front/product.buy_now') }}
                  </button>
                  @hookinsert('product.detail.cart.after')
                </div>
              </div>
            @endif

            <div class="add-wishlist mb-3" data-in-wishlist="{{ $product->hasFavorite() }}"
                 data-id="{{ $product->id }}"
                 data-price="{{ $product->masterSku->price }}">
              <i
                class="bi bi-heart{{ $product->hasFavorite() ? '-fill' : '' }}"></i> {{ __('front/product.add_wishlist') }}
            </div>
            @hookinsert('product.detail.after')
          </div>
        </div>
      </div>
    </div>

    <div class="product-description">
      <ul class="nav nav-tabs tabs-plus">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab"
                  data-bs-target="#product-description-description"
                  type="button">{{ __('front/product.description') }}</button>
        </li>
        @if($attributes)
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#product-description-attribute"
                    type="button">{{ __('front/product.attribute') }}</button>
          </li>
        @endif
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#product-review"
                  type="button">{{ __('front/product.review') }}</button>
        </li>
        <li class="nav-item">
          <button class="nav-link correlation" data-bs-toggle="tab"
                  data-bs-target="#product-description-correlation"
                  type="button">{{__('front/product.related_product')}}
          </button>
        </li>
        @hookinsert('product.detail.tab.link.after')
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade show active" id="product-description-description">
          @if($product->fallbackName('selling_point'))
            {!! parsedown($product->fallbackName('selling_point')) !!}
          @endif
          {!! $product->fallbackName('content') !!}
          @hookinsert('product.detail.description.after')
        </div>

        @if($attributes)
          <div class="tab-pane fade" id="product-description-attribute" role="tabpanel">
            <table class="table table-bordered attribute-table">
              @foreach ($attributes as $group)
                <thead class="table-light">
                <tr>
                  <td colspan="2"><strong>{{ $group['attribute_group_name'] }}</strong></td>
                </tr>
                </thead>
                <tbody>
                @foreach ($group['attributes'] as $item)
                  <tr>
                    <td>{{ $item['attribute'] }}</td>
                    <td>{{ $item['attribute_value'] }}</td>
                  </tr>
                @endforeach
                </tbody>
              @endforeach
            </table>
          </div>
        @endif

        <div class="tab-pane fade" id="product-review" role="tabpanel">
          @include('products.components._review_section')
        </div>
        <div class="tab-pane fade" id="product-description-correlation">
          <div class="row gx-3 gx-lg-4">
            @foreach ($related as $relatedItem)
              <div class="col-6 col-md-4 col-lg-3">
                @include('shared.product', ['product'=>$relatedItem])
              </div>
            @endforeach
          </div>
        </div>
        @hookinsert('product.detail.tab.pane.after')
      </div>
    </div>

    @hookinsert('product.show.bottom')

  </div>

@endsection

@push('footer')
  <script>
    $('.quantity-wrap .plus, .quantity-wrap .minus').on('click', function () {
      if ($(this).parent().hasClass('disabled')) {
        return;
      }

      let quantity = parseInt($(this).siblings('input').val());
      if ($(this).hasClass('plus')) {
        $(this).siblings('input').val(quantity + 1);
      } else {
        if (quantity > 1) {
          $(this).siblings('input').val(quantity - 1);
        }
      }
    });

    $('.add-cart, .buy-now').on('click', function () {
      // 验证必需选项是否已选择
      if (typeof validateRequiredOptions === 'function' && !validateRequiredOptions()) {
        // 滚动到第一个错误的选项组
        const $firstError = $('.option-group.has-error').first();
        if ($firstError.length) {
          $('html, body').animate({
            scrollTop: $firstError.offset().top - 100
          }, 500);
        }
        
        if (window.inno && window.inno.alert) {
          window.inno.alert({msg: '{{ __("front/product.please_select_required_options") }}', type: 'warning'});
        } else {
          alert('{{ __("front/product.please_select_required_options") }}');
        }
        return;
      }

      const quantity = $('.product-quantity').val();
      const skuId = $('.product-quantity').data('sku-id');
      const isBuyNow = $(this).hasClass('buy-now');

      // 收集选中的选项
      const productOptions = {};
      
      // 收集下拉选择框的选项
      $('.option-select').each(function() {
        const optionId = $(this).data('option-id');
        const selectedValue = $(this).val();
        if (selectedValue) {
          productOptions[optionId] = [selectedValue];
        }
      });
      
      // 收集单选按钮的选项
      $('.option-radio-item input[type="radio"]:checked').each(function() {
        const optionId = $(this).data('option-id');
        const optionValue = $(this).val();
        productOptions[optionId] = [optionValue];
      });
      
      // 收集多选复选框的选项
      $('.option-checkbox-item input[type="checkbox"]:checked').each(function() {
        const optionId = $(this).data('option-id');
        const optionValue = $(this).val();
        if (!productOptions[optionId]) {
          productOptions[optionId] = [];
        }
        productOptions[optionId].push(optionValue);
      });

      // 准备请求数据
      const requestData = {
        skuId, 
        quantity, 
        isBuyNow,
        options: productOptions
      };
      
      inno.addCart(requestData, this, function (res) {
        if (isBuyNow) {
          window.location.href = '{{ front_route('carts.index') }}';
        }
      })
    });
  </script>
@endpush
