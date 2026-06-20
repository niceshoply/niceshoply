{{--
===========================================================================
【文件说明】
  分类详情页（商品列表页），展示指定分类下的商品列表，配备：
    左侧筛选侧边栏（价格区间 + 属性筛选）、子分类导航、排序/分页控件、
    分类简介（intro）、商品网格/列表、底部分类详细描述。

【对应路由 / 控制器】
  路由名称  : categories.show
  URL 示例  : /category/{slug}  或  /{lang}/category/{slug}
  控制器    : App\Http\Controllers\Front\CategoryController@show

【可用变量】
  $category        — Category 模型  当前分类对象
                     常用属性:
                       id, fallbackName('name'/'summary'/'content')
                       url, image, description
                       activeChildren（活跃子分类集合）
  $products        — LengthAwarePaginator  商品分页集合（Product 模型）
  $per_page_items  — array   每页可选数量列表，如 [12, 24, 48]
  $filters         — array|null  激活的筛选条件

【URL 查询参数】
  sort        — 排序字段（products.sales / pt.name / ps.price）
  order       — 排序方向（asc / desc）
  per_page    — 每页数量
  style_list  — 显示模式（grid / list）
  price       — 价格范围（由侧边栏写入 URL）
  attributes  — 属性筛选条件

【Sections / Blocks】
  body-class  — 值为 'page-categories'
  title       — SEO 标题（MetaInfo 自动填充）
  description — SEO 描述
  keywords    — SEO 关键词
  content     — 页面主体内容
  footer      — 排序/分页/视图切换的 JS 脚本

【包含的局部模板】
  shared.filter_sidebar                  — 左侧筛选侧边栏
  categories.partials._intro             — 分类简介卡片（分类名 + 摘要）
  categories.partials._subcategories     — 子分类导航卡片
  categories.partials._controls          — 排序/每页数量/视图切换控件栏
  categories.partials._products          — 商品列表 + 分页导航
  categories.partials._description       — 分类底部详细描述（content 字段）

【插件钩子】
  @hookinsert('category.show.top')    — 分类商品页顶部
  @hookinsert('category.show.bottom') — 分类商品页底部（筛选后内容区末尾）

【辅助函数】
  MetaInfo::getInstance($category)->getTitle() — 获取 SEO 标题（优先自定义，否则用分类名）

【自定义建议】
  - 修改侧边栏列宽：调整 col-12 col-md-3 / col-12 col-md-9 的比例。
  - 若不需要筛选侧边栏：移除整个 col-12 col-md-3 块，并将商品列调整为 col-12。
  - 排序和视图切换 JS（filterProductData）逻辑与 products/index.blade.php 完全一致，
    可提取为公共 JS 文件以减少重复。
===========================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-categories')

@section('title', \NiceShoply\Common\Libraries\MetaInfo::getInstance($category)->getTitle())
@section('description', \NiceShoply\Common\Libraries\MetaInfo::getInstance($category)->getDescription())
@section('keywords', \NiceShoply\Common\Libraries\MetaInfo::getInstance($category)->getKeywords())

@section('content')
  <x-front-breadcrumb type="category" :value="$category" :showFilter="true"/>

  @hookinsert('category.show.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-md-3">
        @include('shared.filter_sidebar')
      </div>

      <div class="col-12 col-md-9">
        @include('categories.partials._intro', ['category' => $category])
        @include('categories.partials._subcategories', ['category' => $category])
        @include('categories.partials._controls', ['products' => $products, 'per_page_items' => $per_page_items])
        @include('categories.partials._products', ['products' => $products])
        @include('categories.partials._description', ['category' => $category])
      </div>
    </div>

    @hookinsert('category.show.bottom')

  </div>
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