{{--
================================================================================
【文件说明】
  商品评价表单局部模板 —— 用于用户中心"我的评价"页面和商品详情页的评价弹窗。
  支持两种场景：
    场景一（订单评价）：从订单页发起评价，显示订单商品信息表格（订单号、图片、名称、规格），
                        用于评价特定订单中的商品，表单字段含 order_number、order_item_id、product_sku。
    场景二（商品评价）：从商品详情页直接评价，不显示订单信息表格，
                        表单字段含 product_id。
  两种场景均包含星级评分（1-5 星，默认 5 星）和评价内容文本框。

【引用方式】
  场景一（订单评价）：
    @include('shared.review', ['order' => $order])

  场景二（商品评价）：
    @include('shared.review', ['product' => $product])
    @include('shared.review')  {{-- product 变量由控制器注入时可省略 --}}

【可用变量】
  场景一（订单评价模式）：
    $order                  — 订单对象，用于判断进入"订单评价"模式。
                              传入该变量时渲染订单商品信息表格。
                              表格中的数据（订单号、图片、商品名、规格）由前台 JS
                              动态填充（通过 id 属性：#order_number、#product-image、#name、#label）

  场景二（商品评价模式）：
    $product                — 商品模型对象（可选，若已由控制器注入则不需传参），包含：
      ->id                  — 商品 ID，填充到 input[name="product_id"]

  全局辅助函数：
    account_route('reviews.store') — 生成评价提交的 POST 路由 URL

  多语言翻译 Key：
    front/order.order_number   — 表头"订单号"
    front/order.product_image  — 表头"商品图片"
    front/order.product_name   — 表头"商品名称"
    front/order.product_spec   — 表头"商品规格"
    front/product.input_your_review   — 评价内容标签文字
    front/product.input_some_text_here — 评价文本框占位符
    front/product.submit_review        — 提交按钮文字

【输出内容】
  POST 表单（提交到 account_route('reviews.store')），包含：
  - @csrf CSRF 令牌
  - 隐藏字段（根据场景二选一）：
      order_number + order_item_id + product_sku（订单评价）
      product_id（商品评价）
  - 订单商品信息表格（仅订单评价模式，内容由 JS 填充）
  - 星级评分：1-5 星单选（CSS 星星反向渲染，默认选中 5 星）
  - 评价内容文本框（5 行）
  - 提交按钮

【自定义建议】
  1. 该模板通常嵌套在 Bootstrap Modal 弹窗中使用，
     .modal-body 和 .modal-footer 类由本模板自身提供，
     外层 Modal 容器由父视图负责。
  2. 星级评分使用纯 CSS 技巧（radio + label 反向排列 + :checked ~ label）实现，
     如需自定义样式需在主题 CSS 中覆盖 .rating 相关规则。
  3. 订单评价模式下，表格中的数据（#order_number、#product-image 等）
     需由父视图中的 JS 在弹窗打开时动态填充，例如：
       $('#order_number').text(orderData.number);
       $('input[name="order_item_id"]').val(item.id);
  4. 提交后若需 AJAX 提交（而非原生 form submit），
     可在父视图中监听 .submit_review 按钮并拦截默认行为。
================================================================================
--}}
<form action="{{ account_route('reviews.store') }}" method="POST">
  <div class="modal-body mb-2">
    @csrf
    @if (isset($order))
      <input type="hidden" name="order_number" value="">
      <input type="hidden" name="order_item_id" value="">
      <input type="hidden" name="product_sku" value="">
    @else
      <input type="hidden" name="product_id" value="{{ $product->id ?? '' }}">
    @endif
    <div>
      <div class="review-content">
        @if (isset($order))
          <div class="mb-3">
            <table class="table table-bordered table-striped table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_number') }}</th>
                <th>{{ __('front/order.product_image') }}</th>
                <th>{{ __('front/order.product_name') }}</th>
                <th>{{ __('front/order.product_spec') }}</th>
              </tr>
              </thead>
              <tbody>
              <tr>
                <td data-title="Order number" class="align-items-center" id='order_number'></td>
                <td data-title="product-image">
                  <img class="product-image wh-30 justify-content-center align-items-center" id="product-image"
                       src="" class="img-fluid wh-20">
                </td>
                <td data-title="product-name" class="name align-items-center" id="name"></td>
                <td data-title="product-label" class="label mt-2 text-secondary" id="label"></td>
              </tr>
              </tbody>
            </table>
          </div>
        @endif
        <div class="row">
          <label class="col-8 text-left font-size-25 mb-0" for="review">
            <h5>{{ __('front/product.input_your_review') }}</h5>
          </label>
          <div class="rating col-4 text-end">
            <input type="radio" name="rating" value="5" id="5" checked>
            <label for="5">☆</label>
            <input type="radio" name="rating" value="4" id="4">
            <label for="4">☆</label>
            <input type="radio" name="rating" value="3" id="3">
            <label for="3">☆</label>
            <input type="radio" name="rating" value="2" id="2">
            <label for="2">☆</label>
            <input type="radio" name="rating" value="1" id="1">
            <label for="1">☆</label>
          </div>
        </div>
        <textarea class="form-control" name="content" id="review" rows="5"
                  placeholder="{{ __('front/product.input_some_text_here') }}..."></textarea>
        <div class="mt-3">
          <label class="form-label">{{ __('front/review.images') }}</label>
          <textarea class="form-control" name="images" rows="2" placeholder="{{ __('front/review.images_hint') }}"></textarea>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-footer">
    <div class="col-12 text-end">
      <button type="submit" class="btn btn-primary submit_review">{{ __('front/product.submit_review') }}</button>
    </div>
  </div>
</form>
