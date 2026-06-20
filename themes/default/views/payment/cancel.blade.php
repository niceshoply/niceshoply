{{--
================================================================================
【文件说明】
  支付取消回调页。当用户在第三方支付平台页面主动取消支付（点击"返回商家"或
  关闭支付弹窗）后，平台将用户重定向至此页。显示取消提示及订单概要，
  引导用户重新支付或返回首页继续浏览。

【对应路由 / 控制器】
  路由名称：payment.cancel（或 payments.cancel）
  HTTP 方法：GET（支付平台取消回调 redirect）
  控制器：Front\PaymentController@cancel（或等价方法）

【可用变量】（由控制器注入到视图）
  $order   \App\Models\Order|null   订单对象，常用属性：
             - number        string  订单编号
             - created_at    Carbon  下单时间
             - total         float   订单合计（原始数值）
             - status_format string  订单状态（已翻译，通常仍为"待付款"）
           当 $order 为 null 时页面显示 "No order." 文案

【Sections】
  body-class  → page-checkout-success（可按需改为 page-payment-cancel）
  content     → 页面主体内容

【前端交互】
  本页无 JS 交互，纯静态展示。

【插件钩子】
  @hookinsert('checkout.success.top')    取消框顶部（与成功/失败页共用钩子名）
  @hookinsert('checkout.success.bottom') 取消框底部

【多语言文案 Key】
  front/payment.cancel_title        — 支付取消标题文案
  front/payment.view_order         — "查看订单" 按钮文案
  front/payment.continue_shopping  — "继续购物" 按钮文案

【查看订单按钮路由逻辑】
  已登录用户 → account_route('orders.number_show')（用户中心，可重新发起支付）
  未登录用户 → front_route('orders.number_show')（前台公开订单页）

【与 fail 页面的区别】
  - cancel：用户主动取消，订单通常保持"待付款"状态，可继续支付。
  - fail：支付处理失败（技术错误或卡被拒绝），可能需要换支付方式重试。
  两者共用相同的 HTML 结构，区别仅在于图标（payment-cancel.svg）和标题文案。

【自定义建议】
  1. 取消图标来自 /images/icons/payment-cancel.svg，可替换为自定义图标。
  2. 强烈建议在此页添加"重新支付"快捷按钮，降低取消后的流失率：
       <a href="{{ front_route('orders.pay', ['number'=>$order->number]) }}"
          class="btn btn-lg btn-primary mb-3">继续支付</a>
  3. 可加入挽留文案（如"您的商品已为您保留 X 小时"），配合订单超时配置使用。
  4. 可展示其他可用支付方式列表，引导用户更换支付方式完成下单。
================================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-checkout-success')

@section('content')

  @if($order)
    <x-front-breadcrumb type="order" :value="$order"/>
  @endif

  @hookinsert('checkout.success.top')

  <div class="container">
    <div class="checkout-success-box">
      @if($order)
        <div class="order-success-icon"><img src="{{ asset('/images/icons/payment-cancel.svg') }}" class="img-fluid"></div>
        <div class="checkout-success-title"><span>{{ trans('front/payment.cancel_title') }}</span></div>
        <table class="table w-max-700 mx-auto mb-3 mb-md-5 payment-result-table">
          <thead>
          <tr>
            <th>{{ trans('front/order.order_number') }}</th>
            <th>{{ trans('front/order.order_date') }}</th>
            <th>{{ trans('front/order.order_total') }}</th>
            <th>{{ trans('front/order.order_status') }}</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>{{ $order->number }}</td>
            <td>{{ $order->created_at->format('Y-m-d') }}</td>
            <td>{{ currency_format($order->total) }}</td>
            <td>{{ $order->status_format }}</td>
          </tr>
          </tbody>
        </table>

        <div class="checkout-success-btns d-flex flex-column justify-content-center w-max-400 mx-auto">
          @if(current_customer())
            <a href="{{ account_route('orders.number_show', ['number'=>$order->number]) }}"
               class="btn btn-lg btn-primary mb-3">{{ trans('front/payment.view_order') }}</a>
          @else
            <a href="{{ front_route('orders.number_show', ['number'=>$order->number]) }}"
               class="btn btn-lg btn-primary mb-3">{{ trans('front/payment.view_order') }}</a>
          @endif
          <a href="{{ front_route('home.index') }}" class="btn btn-lg btn-outline-primary">{{ trans('front/payment.continue_shopping') }}</a>
        </div>
      @else
        No order.
      @endif
    </div>
  </div>
  @hookinsert('checkout.success.bottom')
@endsection
