{{--
================================================================================
【文件说明】
  商品卡片局部模板 —— 用于在商品列表、分类页、搜索结果页、首页推荐区等位置
  渲染单个商品的缩略图、名称、价格、加入购物车按钮及收藏按钮。
  支持"网格模式"（默认）和"列表模式"（URL 带 ?style_list=list）两种展示风格。

【引用方式】
  @foreach ($products as $product)
      @include('shared.product')
  @endforeach
  ※ $product 由父级 @foreach 循环注入，无需手动传参。

【可用变量】
  来自父模板 / 循环注入：
    $product                — 商品模型对象，核心数据源，包含：
      ->fallbackName()      — 返回当前语言的商品名称（无翻译时回退到默认语言）
      ->fallbackName('summary') — 商品摘要（列表模式下显示）
      ->url                 — 商品详情页 URL
      ->image_url           — 商品主图 URL
      ->hasHoverImage()     — 是否有鼠标悬停图
      ->getHoverImageUrl()  — 悬停图 URL
      ->hasFavorite()       — 当前用户是否已收藏（需登录）
      ->id                  — 商品 ID
      ->masterSku           — 主 SKU 对象，包含：
          ->price           — SKU 原始价格（数字）
          ->origin_price    — 划线价（促销前原价）
          ->origin_price_format — 格式化后的划线价字符串
          ->getFinalPrice() — 最终售价（数字，含促销折扣）
          ->getFinalPriceFormat() — 格式化后的最终售价字符串
          ->id              — SKU ID

  全局系统函数：
    system_setting('disable_online_order') — 是否禁用线上下单（true 时隐藏加购按钮）
    __('front/product.add_wishlist')        — 多语言翻译：收藏文字
    __('front/cart.add_to_cart')            — 多语言翻译：加入购物车文字

  URL 请求参数：
    request('style_list')  — 展示风格，值为 'list' 时切换为列表模式

【输出内容】
  渲染一个 .product-grid-item 容器，包含：
  - 商品图片区：主图 + 悬停图（可选）+ 收藏按钮（网格模式时显示在图片上方）
  - 商品信息区：名称链接 + 摘要（列表模式）+ 价格 + 加入购物车按钮
  - 列表模式额外在信息区底部再渲染一次收藏按钮

【Hook 扩展点】
  @hookinsert('product.list_item.image.before') — 商品图片之前插入内容
  @hookinsert('product.list_item.name.after')   — 商品名称之后插入内容

【自定义建议】
  1. 修改卡片布局时，注意保留 .add-wishlist[data-id][data-price] 和
     .btn-add-cart[data-id][data-sku-id][data-price] 上的 data 属性，
     前台 JS 依赖这些属性完成收藏与加购交互。
  2. 如需自定义价格展示（如展示折扣百分比），在 .product-price 区域扩展即可。
  3. 悬停图效果由 CSS 控制，.product-hover-image 默认隐藏，
     hover 时通过 CSS 显示，可在主题样式中重写。
  4. style_list=list 时会额外显示 .sub-product-title，
     可在 CMS 列表页通过 URL 参数切换风格，无需修改模板。
================================================================================
--}}
@if($product->fallbackName())
  <div class="product-grid-item {{ request('style_list') ?? '' }}">
    
    <div class="image position-relative">
    @hookinsert('product.list_item.image.before')
      <a href="{{ $product->url }}">
        <img src="{{ $product->image_url }}" class="img-fluid product-main-image">
        @if($product->hasHoverImage())
          <img src="{{ $product->getHoverImageUrl() }}" class="img-fluid product-hover-image">
        @endif
      </a>
      <div class="wishlist-container add-wishlist" data-in-wishlist="{{ $product->hasFavorite() }}"
           data-id="{{ $product->id }}" data-price="{{ $product->masterSku->price }}">
        <i class="bi bi-heart{{ $product->hasFavorite() ? '-fill' : '' }}"></i> {{ __('front/product.add_wishlist') }}
      </div>
    </div>
    <div class="product-item-info">
      <div class="product-name">
        <a href="{{ $product->url }}" data-bs-toggle="tooltip" title="{{ $product->fallbackName() }}"
           data-placement="top">
          {{ $product->fallbackName() }}
        </a>
      </div>

      @hookinsert('product.list_item.name.after')

      @if(request('style_list') == 'list')
        <div class="sub-product-title">{{ $product->fallbackName('summary') }}</div>
      @endif

      <div class="product-bottom">
        @if(!system_setting('disable_online_order'))
          <div class="product-bottom-btns">
            <div class="btn-add-cart cursor-pointer" data-id="{{ $product->id }}"
               data-price="{{ $product->masterSku->getFinalPrice() }}"
               data-sku-id="{{ $product->masterSku->id }}">{{ __('front/cart.add_to_cart') }}
            </div>
          </div>
        @endif
        <div class="product-price">
          @if ($product->masterSku->origin_price)
            <div class="price-old">{{ $product->masterSku->origin_price_format }}</div>
          @endif
          <div class="price-new">{{ $product->masterSku->getFinalPriceFormat() }}</div>
        </div>
      </div>
      @if(request('style_list') == 'list')
        <div class="add-wishlist" data-in-wishlist="{{ $product->hasFavorite() }}" data-id="{{ $product->id }}"
             data-price="{{ $product->masterSku->price }}">
          <i class="bi bi-heart{{ $product->hasFavorite() ? '-fill' : '' }}"></i> {{ __('front/product.add_wishlist') }}
        </div>
      @endif
    </div>
  </div>
@endif
