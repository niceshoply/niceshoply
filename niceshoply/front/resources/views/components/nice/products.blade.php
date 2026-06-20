{{--
================================================================================
【文件说明】
  商品列表展示模块组件（Products Module）。
  在首页或自定义页面中以网格形式展示一组商品卡片，支持自定义标题、
  显示数量（limit）和每行列数（cols）。
  每个商品卡片通过 @include('shared.product') 渲染，样式统一。

  在主题模板中通过 nice 标签调用：
    {nice:products limit="8" cols="4" title="推荐商品"}
    {nice:products limit="6" cols="3" title="新品上架" category_id="12"}
  等价于 Blade 组件：
    <x-nice-products limit="8" cols="4" title="推荐商品" />

【注册方式】
  FrontServiceProvider 中以别名 "nice-products" 注册：
    Blade::component('nice-products', Components\Nice\Products::class);

【可用变量 / Props】
  以下 Props 通过标签属性传入组件类，组件类处理后注入视图：
  - $title        — 模块标题文字（字符串），为空时不显示标题区域
  - $limit        — 显示商品数量（整数，默认 8）
  - $cols         — 每行显示列数（整数，默认 4），可选值：2、3、4、6
                    Bootstrap 列宽计算：col-lg-{{ 12 / $cols }}
  - $category_id  — 按分类 ID 筛选商品（可选，不传则显示全局推荐/最新商品）
  - $products     — 商品对象 Collection（由组件类自动查询并注入），每个对象包含：
                    id、name、slug、image、price、original_price、rating 等属性
                    以及 url 属性（商品详情页链接）

  商品卡片子模板：
  - @include('shared.product') 使用 $product 变量（循环体内自动赋值）
    渲染商品图片、名称、价格、评分等信息，样式由主题 shared/product.blade.php 定义

【插件钩子】
  本组件内部暂无 @hookinsert 点位。

【自定义建议】
  开发新主题时，可以：
  1. 修改商品卡片的 col 断点（col-6 col-md-4 col-lg-*）以调整不同屏幕下的列数。
  2. 在标题区域（.module-title-wrap）添加「查看更多」链接：
     <a href="{{ front_route('products.index') }}" class="more-link">查看更多</a>
  3. 修改 shared/product.blade.php 来统一调整全站商品卡片样式。
  4. 若需按多个条件筛选（如按标签、价格区间），可扩展组件类的查询逻辑。
  5. $cols 影响 Bootstrap 列宽（12 / $cols），建议使用 12 的因数：2、3、4、6。
================================================================================
--}}
<section class="module-line">
  <div class="container">
    @if($title)
      <div class="module-title-wrap">
        <div class="module-title">{{ $title }}</div>
      </div>
    @endif
    <div class="row gx-3 gx-lg-4">
      @foreach ($products as $product)
        <div class="col-6 col-md-4 col-lg-{{ 12 / $cols }}">
          @include('shared.product')
        </div>
      @endforeach
    </div>
  </div>
</section>
