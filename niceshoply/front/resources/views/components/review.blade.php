{{--
================================================================================
【文件说明】
  商品评分星星图标组件（Review / Star Rating）。
  根据传入的评分数值（1-5），渲染对应数量的实心星（bi-star-fill）
  和空心星（bi-star），颜色使用 Bootstrap 主题色变量 --bs-primary。

  在商品卡片、商品详情页、评论列表等处通过 @include 或组件标签调用：
    @include('components.review', ['rating' => $product->rating])
  或通过 Blade 组件调用：
    <x-front-review :rating="$product->rating" />

【注册方式】
  FrontServiceProvider 中以别名 "front-review" 注册：
    Blade::component('front-review', Components\Review::class);

【可用变量 / Props】
  - $rating  — 整数（1-5），代表评分星级数
               实心星数量 = $rating，空心星数量 = 5 - $rating
               建议在传入前对数值进行 round() 取整和范围限制（1 ≤ rating ≤ 5）

【插件钩子】
  本组件内部暂无 @hookinsert 点位。

【自定义建议】
  开发新主题时，可以：
  1. 将 Bootstrap Icons（bi-star-fill / bi-star）替换为 Font Awesome 或自定义 SVG 图标。
  2. 星星颜色默认使用 var(--bs-primary)，可改为固定颜色（如 #FFD700 金黄色）。
  3. 若需支持半星（0.5 步进），可扩展逻辑：先渲染整星，再判断余数是否 >= 0.5
     并渲染半星图标（如 bi-star-half）。
  4. 若评分为 0 或 null，建议在调用处加判断以避免渲染 5 颗空心星误导用户。
================================================================================
--}}

@for($i=1;$i<=$rating;$i++)
  <i class="bi-star-fill" style="color: var(--bs-primary);"></i>
@endfor

@for($i=1;$i<=5-$rating;$i++)
  <i class="bi-star" style="color: var(--bs-primary);"></i>
@endfor