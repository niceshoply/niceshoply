{{--
===========================================================================
【文件说明】
  品牌列表页，按品牌名称首字母分组展示所有品牌，每个品牌显示 Logo 图片和名称。
  页面顶部有首字母快速导航锚点链接，点击可跳转到对应字母分组区域。

【对应路由 / 控制器】
  路由名称  : brands.index
  URL 示例  : /brands  或  /{lang}/brands
  控制器    : App\Http\Controllers\Front\BrandController@index

【可用变量】
  $brands  — array|Collection  按首字母分组的品牌数据，结构：
             [
               'A' => [ Brand, Brand, ... ],
               'B' => [ Brand, ... ],
               ...
             ]
             每个 Brand 模型常用属性:
               id, name（品牌名称）, logo（Logo 图片路径）
               url（品牌详情页完整 URL）
               first（首字母，用于锚点 ID）

【辅助函数】
  image_resize($brand->logo, 200, 200) — 生成 200×200 品牌 Logo 缩略图 URL
  front_route('brands.index')          — 生成品牌列表页 URL（用于锚点链接前缀）

【关键 CSS 类说明】
  .brand-group    — 顶部首字母快速导航按钮组（Bootstrap .btn-group）
  .brands-wrap    — 所有品牌分组的外层容器
  .item           — 单个字母分组容器（含 id="page-brands-{首字母}" 锚点）
  ul > li > a     — 每个品牌的点击区域（图片 + 名称）
  .img > img      — 品牌 Logo 图片（200×200 缩略图）

【锚点导航说明】
  顶部按钮的 href 格式：front_route('brands.index') + '#page-brands-{首字母}'
  对应下方分组的 id="page-brands-{首字母}"
  点击后页面滚动到对应字母分组

【Sections / Blocks】
  body-class — 值为 'page-brands'
  content    — 页面主体内容
  footer     — 预留 JS 块（当前为空，可扩展品牌搜索/过滤逻辑）

【插件钩子】
  @hookinsert('brand.index.top')    — 品牌列表顶部（可插入 Banner、品牌说明等）
  @hookinsert('brand.index.bottom') — 品牌列表底部

【自定义建议】
  - 修改 Logo 尺寸：调整 image_resize() 的宽高参数，同步修改 .img CSS 尺寸限制。
  - 品牌搜索框：在 .brand-group 上方添加搜索表单，使用 JS 实时过滤品牌名称。
  - 修改布局为网格：将 <ul><li> 结构替换为 Bootstrap .row .col，实现更规整的品牌网格展示。
  - 显示品牌商品数量：在 $brand->name 旁添加 <small>（{{ $brand->products_count }}）</small>，
    前提是控制器 withCount('products') 预加载了计数。
===========================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-brands')

@section('content')
<x-front-breadcrumb type="route" value="brands.index" title="{{ __('front/product.brand') }}" />

@hookinsert('brand.index.top')

<div class="container">
  <div class="btn-group brand-group" role="group">
    @foreach($brands as $first => $items)
      <a href="{{ front_route('brands.index') }}#page-brands-{{ $first }}" class="btn">{{ $first }}</a>
    @endforeach
  </div>
  <div class="brands-wrap">
    @foreach($brands as $first=>$items)
    <div class="item" id="page-brands-{{ $first }}">
      <span class="fw-bold fs-4 mb-2">{{ $first }}</span>
      <ul>
        @foreach($items as $brand)
        <li>
          <a href="{{ $brand->url }}" class="text-secondary">
            <div class="img"><img src="{{ image_resize($brand->logo, 200, 200) }}" class="img-fluid" /></div>
            <span>{{ $brand->name }} </span>
          </a>
        </li>
        @endforeach
      </ul>
    </div>
    @endforeach
  </div>
</div>

@hookinsert('brand.index.bottom')

@endsection
@push('footer')
<script>

</script>
@endpush