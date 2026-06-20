{{--
================================================================================
【文件说明】
  结账成功页（下单成功落地页）。用户提交订单后，若订单为"免支付"或特殊流程
  直接完成，则跳转至此页显示订单创建成功的提示信息与订单概要。
  注意：此页为"下单"成功，不代表"付款"成功；付款成功请参见 payment/success.blade.php。

【对应路由 / 控制器】
  路由名称：checkout.success（或等价路由）
  HTTP 方法：GET
  控制器：Front\CheckoutController@success（或订单控制器的 success 方法）

【可用变量】（由控制器注入到视图）
  $order   \App\Models\Order|null   订单对象，包含以下常用属性：
             - number        string   订单编号（如 "ORD-20240529-001"）
             - created_at    Carbon   下单时间
             - total         float    订单合计（原始数值）
             - status        string   订单状态原始值
             注意：当订单不存在时 $order 为 null，页面显示"No order."文案

【Sections】
  body-class  → page-checkout-success
  content     → 页面主体内容

【前端交互】
  本页无复杂 JS 交互，仅静态展示。
  @push('footer') 中预留空 <script> 标签，可按需扩展（如埋点、转化追踪）。

【插件钩子】
  @hookinsert('checkout.success.top')    成功框顶部（适合插入感谢文案/广告）
  @hookinsert('checkout.success.bottom') 成功框底部（适合插入推荐商品/引导注册）

【自定义建议】
  1. 成功图标来自 /images/icons/order-success.svg，可替换为自定义品牌图标。
  2. "查看订单"按钮根据 current_customer() 判断跳转路由：
       - 已登录 → account_route('orders.number_show')（用户中心）
       - 未登录 → front_route('orders.number_show')（前台公开订单页）
  3. 如需展示更多订单详情（商品列表、收货地址等），可通过 $order->items、
     $order->address 等关联属性扩展。
  4. 可在此页集成 Google Analytics / Meta Pixel 的购买转化事件，
     将 $order->total 和 $order->number 传入追踪代码。
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
        <div class="order-success-icon"><img src="{{ asset('/images/icons/order-success.svg') }}" class="img-fluid"></div>
        <div class="checkout-success-title"><span>Thank you. Your order has been received.</span></div>
        <table class="table w-max-700 mx-auto mb-3 mb-md-5 checkout-success-table">
          <thead>
          <tr>
            <th>Order Number</th>
            <th>Order Date</th>
            <th>Order Total</th>
            <th>Order Status</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>{{ $order->number }}</td>
            <td>{{ $order->created_at->format('Y-m-d') }}</td>
            <td>{{ currency_format($order->total) }}</td>
            <td>{{ $order->status }}</td>
          </tr>
          </tbody>
        </table>

        <div class="checkout-success-btns d-flex flex-column justify-content-center w-max-400 mx-auto">
          @if(current_customer())
            <a href="{{ account_route('orders.number_show', ['number'=>$order->number]) }}"
               class="btn btn-lg btn-primary mb-3">View Order</a>
          @else
            <a href="{{ front_route('orders.number_show', ['number'=>$order->number]) }}"
               class="btn btn-lg btn-primary mb-3">View Order</a>
          @endif
          <a href="{{ front_route('home.index') }}" class="btn btn-lg btn-outline-primary">Continue Shopping</a>
        </div>
      @else
        No order.
      @endif
    </div>
  </div>
  @hookinsert('checkout.success.bottom')
@endsection

@push('footer')
  <script>

  </script>
@endpush
