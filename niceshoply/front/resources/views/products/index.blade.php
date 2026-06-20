{{--
===========================================================================
【文件说明】
  商品列表页（全站所有商品汇总列表），支持排序、每页数量、网格/列表视图切换及侧边栏筛选。

【对应路由 / 控制器】
  路由名称  : products.index
  URL 示例  : /products  或  /{lang}/products
  控制器    : App\Http\Controllers\Front\ProductController@index
              （或 Front 模块内同名控制器）

【可用变量】
  $products       — LengthAwarePaginator   商品分页集合，每项为 Product 模型
                    常用属性: id, name, price, image_url, url, masterSku
                    分页方法: firstItem(), lastItem(), total(), links()
  $per_page_items — array                  每页可选数量列表，如 [12, 24, 48]
  $filters        — array|null             当前激活的侧边栏筛选条件
                    示例: ['price_min'=>0, 'price_max'=>999, 'attributes'=>[...]]

【URL 查询参数（request()）】
  sort        — 排序字段（products.sales / pt.name / ps.price）
  order       — 排序方向（asc / desc）
  per_page    — 每页数量
  style_list  — 显示模式（grid / list）
  price       — 价格筛选范围（由侧边栏注入）
  attributes  — 属性筛选（由侧边栏注入）

【Sections / Blocks】
  body-class  — 页面 body 的 CSS 类名，值为 'page-categories'
  content     — 页面主体内容区
  footer      — 页脚 JS 脚本区（排序/分页交互逻辑）

【包含的局部模板】
  shared.filter_sidebar  — 左侧价格/属性筛选侧边栏
  shared.product         — 单个商品卡片（网格或列表样式自适应）
  console::vendor/pagination/bootstrap-4 — 分页组件

【插件钩子】
  @hookinsert('product.index.top')    — 商品列表顶部钩子（可插入横幅、公告等）
  @hookinsert('product.index.bottom') — 商品列表底部钩子（可插入推荐区块等）

【自定义建议】
  - 修改排序选项：在 <select class="form-select order-select"> 中增减 <option>，
    value 格式为 "数据库字段|asc" 或 "数据库字段|desc"。
  - 修改网格列数：调整 col-6 col-md-4 等 Bootstrap 栅格类。
  - 若需面包屑显示自定义标题，修改 x-front-breadcrumb 组件的 title 属性。
  - JS 函数 filterProductData() 负责将下拉选择转换为 URL 参数并刷新页面，
    filterAttrChecked() 供属性侧边栏调用，收集选中的属性筛选值。
===========================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-categories')

@section('content')
  <x-front-breadcrumb type="route" value="products.index" title="{{ __('front/product.products') }}" :showFilter="true"/>

  @hookinsert('product.index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-md-3">
        @include('shared.filter_sidebar')
      </div>
      <div class="col-12 col-md-9">
        <div class="category-wrap">
          <div class="top-order-wrap">
            <div class="d-none d-md-block">
              {{ __('front/common.page_total_show', ['first' => $products->firstItem(), 'last' => $products->lastItem(), 'total' => $products->total()]) }}
            </div>
            <div class="right">
              <div class="order-item">
                <span class="d-none d-md-block">{{ __('front/common.sort') }}:</span>
                <select class="form-select order-select">
                  <option value="">{{ __('/front/category.default') }}</option>
                  <option
                      value="products.sales|asc" {{ request('sort') == 'products.sales' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.sales') }}
                    ({{ __('/front/category.low') . ' - ' . __('/front/category.high')}})
                  </option>
                  <option
                      value="products.sales|desc" {{ request('sort') == 'products.sales' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.sales') }}
                    ({{ __('/front/category.high') . ' - ' . __('/front/category.low')}})
                  </option>
                  <option
                      value="pt.name|asc" {{ request('sort') == 'pt.name' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.name') }}
                    (A - Z)
                  </option>
                  <option
                      value="pt.name|desc" {{ request('sort') == 'pt.name' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.name') }}
                    (Z - A)
                  </option>
                  <option
                      value="ps.price|asc" {{ request('sort') == 'ps.price' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.price') }}
                    ({{ __('/front/category.low') . ' - ' . __('/front/category.high')}})
                  </option>
                  <option
                      value="ps.price|desc" {{ request('sort') == 'ps.price' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.price') }}
                    ({{ __('/front/category.high') . ' - ' . __('/front/category.low')}})
                  </option>
                </select>
              </div>
              <div class="order-item">
                <span class="d-none d-md-block">{{ __('front/common.show') }}:</span>
                <select class="form-select per-page-select">
                  @foreach ($per_page_items as $val)
                    <option value="{{ $val }}" {{ request('per_page') == $val ? 'selected' : '' }}>{{ $val }}</option>
                  @endforeach
                </select>
              </div>
              <div class="order-item">
                <label href="javascript:void(0)"
                       class="order-icon {{ !request('style_list') || request('style_list') == 'grid' ? 'active' : ''}}">
                  <i class="bi bi-grid"></i>
                  <input class="d-none" value="grid" type="radio" name="style_list">
                </label>

                <label href="javascript:void(0)"
                       class="order-icon {{ request('style_list') && request('style_list') == 'list' ? 'active' : ''}}">
                  <i class="bi bi-list"></i>
                  <input class="d-none" value="list" type="radio" name="style_list">
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="row gx-3 gx-lg-4 {{ request('style_list') == 'list' ? 'product-list-wrap' : ''}}">
          @foreach ($products as $product)
            <div class="{{ !request('style_list') || request('style_list') == 'grid' ? 'col-6 col-md-4' : 'col-12'}}">
              @include('shared.product')
            </div>
          @endforeach
        </div>

        {{ $products->links('console::vendor/pagination/bootstrap-4') }}
      </div>
    </div>
  </div>

  @hookinsert('product.index.bottom')

@endsection

@push('footer')
  <script>
    $('.form-select, input[name="style_list"]').change(function (event) {
      filterProductData();
    });

    function filterProductData() {
      let url = inno.removeURLParameters(window.location.href, 'price', 'sort', 'order');
      let order = $('.order-select').val();
      let perPage = $('.per-page-select').val();
      let styleList = $('input[name="style_list"]:checked').val();

      layer.load(2, {shade: [0.3, '#fff']})

      if (order) {
        let orderKeys = order.split('|');
        url = inno.updateQueryStringParameter(url, 'sort', orderKeys[0]);
        url = inno.updateQueryStringParameter(url, 'order', orderKeys[1]);
      }

      if (perPage) {
        url = inno.updateQueryStringParameter(url, 'per_page', perPage);
      }

      if (styleList) {
        url = inno.updateQueryStringParameter(url, 'style_list', styleList);
      }

      location = url;
    }

    function filterAttrChecked(data) {
      let filterAtKey = [];
      data.forEach((item) => {
        let checkedAtValues = [];
        item.values.forEach((val) => val.selected ? checkedAtValues.push(val.id) : '')
        if (checkedAtValues.length) {
          filterAtKey.push(`${item.id}:${checkedAtValues.join(',')}`)
        }
      })

      return filterAtKey.join('|')
    }
  </script>
@endpush