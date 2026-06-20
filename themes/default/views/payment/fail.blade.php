{{--
================================================================================
【文件说明】
  支付失败回调页。第三方支付平台在支付失败（如卡被拒绝、余额不足、超时等）后
  将用户重定向至此页，显示支付失败提示及订单概要，引导用户重新支付或返回首页。

【对应路由 / 控制器】
  路由名称：payment.fail（或 payments.fail）
  HTTP 方法：GET（支付平台失败回调 redirect）
  控制器：Front\PaymentController@fail（或等价方法）

【可用变量】（由控制器注入到视图）
  $order   \App\Models\Order|null   订单对象，常用属性：
             - number        string  订单编号
             - created_at    Carbon  下单时间
             - total         float   订单合计（原始数值）
             - status_format string  订单状态（已翻译，通常为"待付款"）
           当 $order 为 null 时页面显示 "No order." 文案

【Sections】
  body-class  → page-checkout-success（可按需改为 page-payment-fail）
  content     → 页面主体内容

【前端交互】
  本页无 JS 交互，纯静态展示。

【插件钩子】
  @hookinsert('checkout.success.top')    失败框顶部（与成功页共用钩子名）
  @hookinsert('checkout.success.bottom') 失败框底部

【多语言文案 Key】
  front/payment.fail_title          — 支付失败标题文案
  front/payment.view_order         — "查看订单" 按钮文案
  front/payment.continue_shopping  — "继续购物" 按钮文案

【查看订单按钮路由逻辑】
  已登录用户 → account_route('orders.number_show')（含重新支付入口）
  未登录用户 → front_route('orders.number_show')

【自定义建议】
  1. 失败图标来自 /images/icons/payment-fail.svg，可替换为自定义图标。
  2. 建议在页面中添加"重新支付"直达按钮，链接到 front_route('orders.pay')，
     方便用户一键重试，减少流失。例如：
       <a href="{{ front_route('orders.pay', ['number'=>$order->number]) }}"
          class="btn btn-lg btn-danger mb-3">重新支付</a>
  3. 可展示失败原因（如果支付网关返回了具体错误码），通过控制器传入 $fail_reason
     变量并在此处渲染。
  4. 可集成客服入口（在线聊天）帮助用户解决支付问题，适合高客单价商品场景。
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
        <div class="order-success-icon"><img src="{{ asset('/images/icons/payment-fail.svg') }}" class="img-fluid"></div>
        <div class="checkout-success-title"><span>{{ trans('front/payment.fail_title') }}</span></div>
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
