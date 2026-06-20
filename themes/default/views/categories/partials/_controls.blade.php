{{--
===========================================================================
【文件说明】
  商品列表排序与显示控制栏局部模板，包含：
    - 当前页商品数量信息（第 N-M 条，共 X 条）
    - 排序下拉框（按销量/名称/价格 升序或降序）
    - 每页显示数量选择器
    - 网格/列表视图切换按钮
  由 categories/show.blade.php 通过 @include('categories.partials._controls') 引入。
  brands/show.blade.php 中内联了相同的结构（功能一致）。

【来源视图】
  categories/show.blade.php

【传入变量】
  $products        — LengthAwarePaginator  商品分页集合
                     使用方法: firstItem(), lastItem(), total()
  $per_page_items  — array  每页可选数量，如 [12, 24, 48]

【URL 查询参数（request()）】
  sort        — 当前排序字段，用于判断 selected 状态
  order       — 当前排序方向（asc / desc）
  per_page    — 当前每页数量
  style_list  — 当前显示模式（grid / list）

【排序 option value 格式】
  "字段名|方向"，在父视图 JS 的 filterProductData() 函数中通过 split('|') 解析
  可用字段:
    products.sales — 按销量排序（数据库别名）
    pt.name        — 按商品名称排序（product_translations 表别名）
    ps.price       — 按价格排序（product_skus 表别名）

【关键 CSS 类说明】
  .category-wrap    — 控制栏外层容器
  .top-order-wrap   — 控制栏内层（flex 布局，左：数量信息，右：控制项）
  .order-select     — 排序下拉框（JS 通过此类名读取值）
  .per-page-select  — 每页数量下拉框（JS 通过此类名读取值）
  .order-icon       — 视图切换图标按钮（.active 表示当前激活）
  input[name="style_list"] — 隐藏 radio 按钮，值为 'grid' 或 'list'

【Sections / Blocks】
  无（依赖父视图 @push('footer') 中的 filterProductData() JS 函数）

【插件钩子】
  无

【自定义建议】
  - 新增排序选项：添加 <option value="新字段|方向">...</option>，
    并确保控制器中支持对应字段的 orderBy 处理。
  - 修改默认每页数量：在控制器中设置 $per_page_items 数组，或调整 URL 默认值。
  - 新增显示模式（如大图模式）：添加新的 radio input 和对应图标，
    在 filterProductData() 中处理新的 style_list 值，并在 _products 中添加对应样式类。
===========================================================================
--}}
{{-- Product sorting and filter control area --}}
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