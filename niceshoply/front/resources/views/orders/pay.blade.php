{{--
================================================================================
【文件说明】
  订单支付页。用户提交订单后跳转至此，展示待支付订单的概要信息，并嵌入对应
  支付方式的支付表单/按钮（由支付插件动态提供）。支持二次支付（已下单但未付款
  的订单可再次访问此页发起支付）。

【对应路由 / 控制器】
  路由名称：orders.pay
  路由参数：{number} — 订单编号
  HTTP 方法：GET
  控制器：Front\OrderController@pay（或 PaymentController@show）

【可用变量】（由控制器注入到视图）
  $order          \App\Models\Order   订单对象，常用属性：
                    - number               string  订单编号
                    - billing_method_name  string  支付方式名称（如 "Stripe"、"PayPal"）
                    - total                float   应付金额（原始数值）
                    - status_format        string  订单状态（已翻译）
  $view_path      string|null   支付插件提供的子视图路径（如 "payment.stripe.form"）；
                                为 null 时不显示支付表单
  $view_data      array|null    传递给支付子视图的数据（如 client_secret、公钥等）；
                                为 null 时不显示支付表单
  $error          string|null   控制器层面的错误信息（非表单验证错误）

【Sections】
  body-class  → page-checkout-success（与结账成功页共用，可按需创建独立 CSS 类）
  content     → 页面主体内容

【前端交互】
  本页自身无 Vue/Alpine 交互，JS 逻辑由 $view_path 嵌入的支付子视图决定。
  例如：Stripe 子视图会注入 Stripe.js 并处理支付表单提交。
  @push('footer') 预留空 <script> 标签，可注入支付前的公共初始化代码。

【插件钩子】
  @hookinsert('order.pay.top')     支付内容区顶部（适合插入安全支付徽标、说明文字）
  @hookinsert('order.pay.bottom')  支付内容区底部（适合插入帮助链接、客服入口）

【支付子视图机制】
  支付插件通过向控制器返回 $view_path 和 $view_data 来注入支付界面：
    @include($view_path, $view_data)
  开发新支付插件时，需提供对应的 Blade 视图文件，并在控制器中正确设置这两个变量。

【自定义建议】
  1. 支付信息表格（checkout-success-table）样式可与结账成功页复用，
     如需差异化可为此页添加独立 CSS 类（如 page-order-pay）。
  2. 若需要展示倒计时（订单超时自动取消），可在此页 @push('footer') 中
     添加倒计时脚本，基于 $order->expires_at 或后端配置的超时时间。
  3. 已完成支付的订单访问此页时，建议在控制器中重定向到 payment/success 或
     订单详情页，避免重复支付。
================================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-checkout-success')

@section('content')

  <x-front-breadcrumb type="static" value="{{ front_route('orders.pay', ['number'=>$order->number]) }}" title="{{ $order->number }}"/>

  @hookinsert('order.pay.top')

  <div class="container mb-5">
    @error('error')
    <div class="alert alert-danger">
      {{ $message }}
    </div>
    @enderror

    @if(isset($error))
      {{ $error }}
    @endif

    <table class="table w-max-800 mx-auto mb-3 mb-md-5 checkout-success-table">
      <thead>
      <tr>
        <th>{{ __('front/order.order_number') }}</th>
        <th>{{ __('front/order.order_billing') }}</th>
        <th>{{ __('front/order.order_total') }}</th>
        <th>{{ __('front/order.order_status') }}</th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td>{{ $order->number }}</td>
        <td>{{ $order->billing_method_name }}</td>
        <td>{{ currency_format($order->total) }}</td>
        <td>{{ $order->status_format }}</td>
      </tr>
      </tbody>
    </table>

    <div class="d-flex flex-column justify-content-center w-max-800 mx-auto">
      @if(isset($view_path) && isset($view_data))
        @include($view_path, $view_data)
      @endif
    </div>
  </div>

  @hookinsert('order.pay.bottom')

@endsection

@push('footer')
  <script></script>
@endpush