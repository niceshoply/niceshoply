{{--
================================================================================
【文件说明】
  支付成功回调页。第三方支付平台（Stripe、PayPal 等）在支付完成后将用户重定向
  至此页面，或系统在异步通知校验成功后展示此页。
  显示订单概要并提供"查看订单"和"继续购物"按钮。
  注意：对于银行转账（bank_transfer），标题文案有所不同（等待汇款确认）。

【对应路由 / 控制器】
  路由名称：payment.success（或 payments.success）
  HTTP 方法：GET（支付平台 redirect 回调）
  控制器：Front\PaymentController@success（或等价方法）

【可用变量】（由控制器注入到视图）
  $order   \App\Models\Order|null   订单对象，常用属性：
             - number                string  订单编号
             - created_at            Carbon  下单时间
             - total                 float   订单合计（原始数值）
             - status_format         string  订单状态（已翻译）
             - payment_method_code   string  支付方式代码（如 "bank_transfer"、"stripe"）
           当 $order 为 null 时页面显示 "No order." 文案

【Sections】
  body-class  → page-checkout-success
  content     → 页面主体内容

【前端交互】
  本页无 JS 交互，纯静态展示。
  可在此处集成 Google Analytics / Meta Pixel 购买转化事件（需在 @push 中注入）。

【插件钩子】
  @hookinsert('checkout.success.top')    成功框顶部（与结账成功页共用钩子名，
                                          适合插入追踪像素、感谢横幅）
  @hookinsert('checkout.success.bottom') 成功框底部（适合插入推荐商品、引导关注）

【多语言文案 Key】
  front/payment.bank_transfer_success_title  — 银行转账成功标题（等待汇款）
  front/payment.success_title               — 在线支付成功标题
  front/payment.view_order                  — "查看订单" 按钮文案
  front/payment.continue_shopping          — "继续购物" 按钮文案

【查看订单按钮路由逻辑】
  已登录用户 → account_route('orders.number_show')（用户中心订单详情）
  未登录用户 → front_route('orders.number_show')（前台公开订单详情）

【自定义建议】
  1. 成功图标来自 /images/icons/payment-success.svg，可替换品牌专属图标。
  2. 银行转账场景下，可在页面中额外展示汇款账户信息（银行名、账号、参考编号），
     建议通过 $order->payment_extra 或系统配置变量获取。
  3. 可在此页加入邮件订阅引导，将 $order->customer_email 预填入订阅表单。
  4. 如需 A/B 测试不同感谢页，可在控制器中随机分配并传递 $variant 变量控制展示。
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
        <div class="order-success-icon"><img src="{{ asset('/images/icons/payment-success.svg') }}" class="img-fluid"></div>
        @if($order->payment_method_code == 'bank_transfer')
          <div class="checkout-success-title"><span>{{ trans('front/payment.bank_transfer_success_title') }}</span></div>
        @else
          <div class="checkout-success-title"><span>{{ trans('front/payment.success_title') }}</span></div>
        @endif
        <table class="table w-max-700 mx-auto mb-3 mb-md-5 checkout-success-table">
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
