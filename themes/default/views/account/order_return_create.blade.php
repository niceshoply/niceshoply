{{--
  ============================================================
  【文件说明】
    用户中心 — 申请退货/退款（创建 RMA）页。
    展示该订单所有商品的购买数量、已退货数量、可退货数量，
    并提供退货申请表单（选择商品、填写数量、是否已拆封、退货原因、备注）。
    若订单所有商品均已退完，则不显示申请表单。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET） ：account.order_returns.create（含 ?order_number=xxx 参数）
    路由名称（POST）：account.order_returns.store
    URL 示例：/{locale}/account/order-returns/create?order_number=ORD-001
    控制器：Front\Account\OrderReturnController@create / @store

  【可用变量】
    $order    — 订单模型对象，含：
                  items        — 订单商品集合，每项含：
                                   name                商品名称
                                   product_sku         SKU 编号
                                   quantity            购买数量
                                   returns             已申请退货记录集合（sum('quantity') 得已退数）
    $number   — string，订单编号（用于页面显示和跳转链接）
    $options  — array，可退货商品的下拉选项（key/label 结构，传给 x-common-form-select）
    $reasons  — Collection，预设退货原因列表，每项含：
                  id          原因 ID
                  name        原因名称
                  description 原因详细说明（选择后在 #reason-description 中显示）

  【Sections】
    body-class → 'page-order'
    content    → 商品可退情况汇总表 + 退货申请表单
    footer     → 退货原因选择联动说明文字显示 JS（@push，仅当 $reasons->count() > 0 时推送）

  【插件钩子】
    @hookinsert('account.order_return_create.top')    — 容器顶部
    @hookinsert('account.order_return_create.bottom') — 容器底部

  【表单字段】
    order_item_id — 选择要退的商品（下拉选择，必填）
    quantity      — 退货数量（数字，必填）
    opened        — 是否已拆封（开关单选，必填）
    reason_id     — 退货原因 ID（下拉选择，可选，仅 $reasons 存在时显示）
    comment       — 退货说明/备注（文本域，必填）

  【自定义建议】
    - 退货原因选择后，#reason-description 会显示该原因的详细说明
    - 若需要上传退货凭证图片，可在表单中添加 <input type="file"> 并在控制器中处理
    - $item->returns->sum('quantity') 计算已退数量，可与 $item->quantity 对比决定是否显示表单
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 创建退货/退款申请（RMA）页。
    展示选定订单中可退货的商品明细表，并提供退货表单（选商品、填数量、选原因、填备注）。
    仅当订单状态为 completed 时，才允许从 order_info 页进入此页面。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.order_returns.create?order_number=xxx
    路由名称（POST）：account.order_returns.store
    URL 示例：/{locale}/account/order-returns/create?order_number=ORD-xxxx
    控制器：Front\Account\OrderReturnController@create / @store

  【可用变量】
    $number   — 关联的订单编号字符串
    $order    — 订单对象（含 items 集合，每条含 name/product_sku/quantity/returns）
    $options  — 商品选项数组（用于 x-common-form-select 下拉列表，key=order_item_id, label=商品名）
    $reasons  — 退货原因集合（含 id/name/description，为空则不显示原因选项）

  【Sections】
    body-class → 'page-order'
    content    → 可退商品汇总表 + 退货表单
    footer     → 退货原因描述联动 JS（@push，仅当 $reasons 非空时输出）

  【插件钩子】
    @hookinsert('account.order_return_create.top')    — 内容顶部
    @hookinsert('account.order_return_create.bottom') — 内容底部

  【表单字段】
    order_item_id — 选择要退货的商品（下拉列表，来自 $options）
    quantity      — 退货数量
    opened        — 商品是否已拆封（switch radio）
    reason_id     — 退货原因 ID（可选，来自 $reasons）
    comment       — 退货备注（必填）

  【自定义建议】
    - 选择退货原因后，JS 会自动显示该原因的 description 作为提示文案
    - 若需支持图片上传（如退货凭证），在表单中添加 x-common-form-imagep 组件
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.order_returns.index" title="{{ __('front/account.order_returns') }}"/>

  @hookinsert('account.order_return_create.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box order-box">
          @if (session()->has('errors'))
            <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4"/>
          @endif
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4"/>
          @endif

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.order_returns') }}</span>
            <span class="fs-6">Order Number: <a
                  href="{{ account_route('orders.number_show', $number) }}">{{ $number }}</a></span>
          </div>

          <table class="table table-bordered table-striped mb-3 table-response">
            <thead>
            <tr>
              <th>{{__('common/rma.purchase_commodity')}}</th>
              <th>{{__('common/rma.purchase_quantity')}}</th>
              <th>{{__('common/rma.returned_quantity')}}</th>
              <th>{{__('common/rma.returnable_quantity')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
              <tr>
                <td>{{ $item->name . ' - ' . $item->product_sku }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->returns->sum('quantity') }}</td>
                <td>{{ $item->quantity - ($item->returns->sum('quantity')) }}</td>
              </tr>
            @endforeach
            </tbody>
          </table>

          @if($item->returns->sum('quantity') < $item->quantity)
            <form class="needs-validation edit-form" action="{{ account_route('order_returns.store') }}" method="POST"
                  novalidate>
              @csrf

              <div class="row">
                <div class="col-12 col-lg-6">
                  <x-common-form-select title="{{__('front/cart.product')}}" name="order_item_id" :options="$options"
                                        key="key" label="label" :emptyOption="false"
                                        required placeholder="{{__('front/cart.product')}}"/>
                </div>
                <div class="col-12 col-lg-6">
                  <x-common-form-input name="quantity" title="{{__('front/return.quantity')}}"
                                       value="{{ old('quantity', 1) }}" required="required"
                                       placeholder="{{ __('front/return.return_number') }}"/>
                </div>
                <div class="col-12 col-lg-6">
                  <x-common-form-switch-radio name="opened" title="{{__('front/return.opened')}}"
                                              value="{{ old('opened', 1) }}" required="required"
                                              placeholder="{{ __('front/return.return_number') }}"/>
                </div>
                @if($reasons->count())
                <div class="col-12 col-lg-6">
                  <x-common-form-select title="{{__('front/return.reason')}}" name="reason_id" :options="$reasons"
                                        key="id" label="name"
                                        :value="old('reason_id', '')"
                                        placeholder="{{__('front/return.please_select_reason')}}"/>
                  <div id="reason-description" class="form-text text-muted mt-1" style="display:none;"></div>
                </div>
                @endif
                <div class="col-12">
                  <x-common-form-textarea name="comment" title="{{__('front/return.comment')}}"
                                          value="{{ old('comment', '') }}" required="required"/>
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-lg mt-4 w-50">{{ __('front/common.submit') }}</button>
            </form>
          @endif

        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.order_return_create.bottom')

@endsection

@if($reasons->count())
@push('footer')
<script>
  (function() {
    const reasonDescriptions = @json($reasons->pluck('description', 'id'));
    const select = document.querySelector('select[name="reason_id"]');
    const hint = document.getElementById('reason-description');
    if (!select || !hint) return;

    select.addEventListener('change', function() {
      const desc = reasonDescriptions[this.value] || '';
      if (desc) {
        hint.textContent = desc;
        hint.style.display = 'block';
      } else {
        hint.style.display = 'none';
      }
    });
  })();
</script>
@endpush
@endif