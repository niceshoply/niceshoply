{{--
===========================================================================
【文件说明】
  品牌详情页（品牌商品列表页），展示指定品牌的介绍信息和该品牌下的全部商品，
  配备左侧筛选侧边栏、排序/每页数量/视图切换控件、商品网格/列表及分页导航。
  结构与 categories/show.blade.php 高度相似，可参考其注释。

【对应路由 / 控制器】
  路由名称  : brands.show
  URL 示例  : /brand/{slug}  或  /{lang}/brand/{slug}
  控制器    : App\Http\Controllers\Front\BrandController@show

【可用变量】
  $brand           — Brand 模型  当前品牌对象
                     常用属性:
                       id, name, logo（图片路径）
                       description（富文本品牌描述）
                       first（首字母，用于品牌导航锚点）
                       url（品牌详情页 URL）
  $products        — LengthAwarePaginator  品牌商品分页集合（Product 模型）
  $per_page_items  — array   每页可选数量列表，如 [12, 24, 48]

【URL 查询参数】
  sort        — 排序字段（products.sales / pt.name / ps.price）
  order       — 排序方向（asc / desc）
  per_page    — 每页数量
  style_list  — 显示模式（grid / list）
  price       — 价格筛选范围
  attributes  — 属性筛选条件

【Sections / Blocks】
  body-class  — 值为 'page-categories'（品牌页复用分类页样式）
  title       — SEO 标题（MetaInfo 自动从 $brand 生成）
  description — SEO 描述
  keywords    — SEO 关键词
  content     — 页面主体内容
  footer      — 排序/分页/视图切换 JS 脚本（与分类页逻辑完全一致）

【包含的局部模板】
  shared.filter_sidebar              — 左侧筛选侧边栏（价格 + 属性）
  brands.partials._intro             — 品牌介绍卡片（Logo + 名称 + 描述）
  shared.product                     — 单个商品卡片（内联遍历渲染）
  console::vendor/pagination/bootstrap-4 — 分页组件

【插件钩子】
  @hookinsert('brand.show.top')    — 品牌商品页顶部
  @hookinsert('brand.show.bottom') — 品牌商品页底部

【自定义建议】
  - 品牌页与分类页的排序控件 HTML 完全重复，建议提取为公共 partials 复用
    （如 shared.partials._product_controls）。
  - 修改品牌 intro 展示方式：编辑 brands/partials/_intro.blade.php。
  - 在品牌页显示"返回品牌列表"链接：在面包屑后添加
    <a href="{{ front_route('brands.index') }}">← 所有品牌</a>。
===========================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-categories')

@section('title', \NiceShoply\Common\Libraries\MetaInfo::getInstance($brand)->getTitle())
@section('description', \NiceShoply\Common\Libraries\MetaInfo::getInstance($brand)->getDescription())
@section('keywords', \NiceShoply\Common\Libraries\MetaInfo::getInstance($brand)->getKeywords())

@section('content')
<x-front-breadcrumb type="brand" :value="$brand" :showFilter="true" />

@hookinsert('brand.show.top')

<div class="container">
  <div class="row">
    <div class="col-12 col-md-3">
      @include('shared.filter_sidebar')
    </div>
    <div class="col-12 col-md-9">
      @include('brands.partials._intro', ['brand' => $brand])
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
                <option value="products.sales|asc" {{ request('sort') == 'products.sales' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.sales') }} ({{ __('/front/category.low') . ' - ' . __('/front/category.high')}})</option>
                <option value="products.sales|desc" {{ request('sort') == 'products.sales' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.sales') }} ({{ __('/front/category.high') . ' - ' . __('/front/category.low')}})</option>
                <option value="pt.name|asc" {{ request('sort') == 'pt.name' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.name') }} (A - Z)</option>
                <option value="pt.name|desc" {{ request('sort') == 'pt.name' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.name') }} (Z - A)</option>
                <option value="ps.price|asc" {{ request('sort') == 'ps.price' && request('order') == 'asc' ? 'selected' : '' }}>{{ __('/front/category.price') }} ({{ __('/front/category.low') . ' - ' . __('/front/category.high')}})</option>
                <option value="ps.price|desc" {{ request('sort') == 'ps.price' && request('order') == 'desc' ? 'selected' : '' }}>{{ __('/front/category.price') }} ({{ __('/front/category.high') . ' - ' . __('/front/category.low')}})</option>
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
              <label href="javascript:void(0)" class="order-icon {{ !request('style_list') || request('style_list') == 'grid' ? 'active' : ''}}">
                <i class="bi bi-grid"></i>
                <input class="d-none" value="grid" type="radio" name="style_list">
              </label>

              <label href="javascript:void(0)" class="order-icon {{ request('style_list') && request('style_list') == 'list' ? 'active' : ''}}">
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

@hookinsert('brand.show.bottom')

@endsection

@push('footer')
<script>
  $('.form-select, input[name="style_list"]').change(function(event) {
    filterProductData();
  });

  function filterProductData() {
    let url = inno.removeURLParameters(window.location.href, 'price', 'sort', 'order');
    let order = $('.order-select').val();
    let perPage = $('.per-page-select').val();
    let styleList = $('input[name="style_list"]:checked').val();

    layer.load(2, {shade: [0.3,'#fff'] })

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