{{--
===========================================================================
【文件说明】
  商品评价列表渲染组件，遍历 $reviews 集合，逐条渲染评价者姓名、评分星级、
  发布日期和评价内容。此组件也被 Ajax "加载更多"接口动态返回的 HTML 所复用。
  由 products/components/_review_section.blade.php 初始引入，
  "加载更多"时由后端直接渲染此模板并返回 HTML 片段追加到前端。

【来源视图】
  products/components/_review_section.blade.php（初始加载）
  以及后端 Ajax 接口（加载更多时动态渲染）

【传入变量】
  $reviews  — Collection | LengthAwarePaginator  评价集合（当前页数据）
              每项为 ProductReview 模型，常用属性：
                $review->customer        : Customer|null  评价用户（可能为 null）
                $review->customer->name  : string         用户昵称
                $review->rating          : int            评分（1-5）
                $review->content         : string         评价内容文本
                $review->created_at      : Carbon         发布时间（自动格式化为字符串）

【全局辅助函数】
  __('front/common.anonymous') — 匿名用户的显示名称翻译

【Blade 组件】
  <x-front-review :rating="$review->rating" /> — 星级展示组件
    Props: rating（int 1-5）— 渲染对应数量的实心/空心星图标

【关键 CSS 类说明】
  .review-item        — 单条评价外层容器
  .review-list        — 评价内容行（Bootstrap .row 布局）
  h5.col-2            — 评价者姓名
  span.col-4          — 星级评分组件容器
  span.col-6.date     — 评价日期（右对齐）
  p.mb-3              — 评价内容正文

【Sections / Blocks】
  无（纯局部模板，不定义任何 section 或 @push）

【插件钩子】
  无

【自定义建议】
  - 显示评价图片：在 $review->content 下方添加 @if($review->images) 循环渲染图片。
  - 显示评价标签（如"真实买家"徽章）：在用户姓名旁添加条件渲染的 badge。
  - 时间格式化：$review->created_at 可调用 ->diffForHumans() 显示相对时间，
    或 ->format('Y-m-d') 显示固定格式。
  - 分页模式（非"加载更多"）：在 _review_section.blade.php 中改用 $reviews->links() 渲染分页导航。
===========================================================================
--}}
@foreach($reviews as $review)
  <div class="review-item">
    <br/>
    <hr/>
    <div class="review-list row">
      <div class="row">
        <h5 class="col-2 mb-3">{{ $review->customer?->name ?? __('front/common.anonymous') }}</h5>
        <span class="col-4 text-left"><x-front-review :rating="$review->rating"/></span>
        <span class="col-6 text-end date">{{ $review->created_at }}</span>
      </div>
      <p class="mb-3">{{ $review['content'] }}</p>
    </div>
  </div>
@endforeach