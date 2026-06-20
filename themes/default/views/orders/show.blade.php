{{--
================================================================================
【文件说明】
  订单详情页（前台公开版）。展示单笔订单的完整信息，包括订单基本信息（编号、
  日期、状态、备注）、商品明细列表（含自定义选项）、各项费用汇总（运费、税等）
  以及订单总额。此页面适用于未登录用户通过订单链接查看订单，或登录用户在前台
  直接访问订单详情。

【对应路由 / 控制器】
  路由名称：orders.number_show（或 front.orders.show）
  路由参数：{number} — 订单编号
  HTTP 方法：GET
  控制器：Front\OrderController@show（或等价方法）

【可用变量】（由控制器注入到视图）
  $order   \App\Models\Order   订单对象，包含以下常用属性与关联：
             - number          string   订单编号
             - created_at      Carbon   下单时间
             - total           float    合计（原始数值）
             - total_format    string   合计（已格式化，含货币符号）
             - status_format   string   订单状态（已翻译的可读文本）
             - comment         string   订单备注（可能较长，页面用 sub_string() 截断）
             - items           array    商品行列表，每项包含：
                                          image, name, product_sku, variant_label,
                                          price_format, quantity,
                                          options[] → {option_name, option_value_name, price_adjustment}
             - fees            Collection  费用行列表（运费、税、折扣等），每项包含：
                                          title, value_format

【Sections】
  body-class  → page-checkout-success（与结账成功页共用同一 CSS 类，可按需修改）
  content     → 页面主体内容

【前端交互】
  本页无 Vue/Alpine 交互，使用 Bootstrap 5 Tooltip 展示完整备注：
    data-bs-toggle="tooltip"  data-bs-title="{{ sub_string($order->comment, 380) }}"
  @push('footer') 预留空 <script> 标签，可扩展（如打印、分享功能）。

【插件钩子】
  @hookinsert('order.show.top')   订单详情顶部（可插入物流追踪入口、操作按钮）
  注意：文件中 @hookinsert('order.show.top') 出现两次（底部重复），第二处
  可改为 'order.show.bottom' 以区分上下钩子位置。

【辅助函数】
  sub_string($str, $length)   截断字符串（默认长度由函数决定），用于备注展示
  currency_format($price)     等同 price_format()，格式化价格

【自定义建议】
  1. 如需显示收货地址，可通过 $order->address（或 $order->shipping_address）
     关联属性获取地址数据并渲染。
  2. 订单状态样式可通过 $order->status_format 对应的 CSS class 区分颜色，
     例如：已完成=绿色、待付款=橙色、已取消=红色。
  3. 商品小计列（第4列 td）当前显示 price_format（单价），正确应为 subtotal；
     如需修正可改为 $product['quantity'] * $product['price'] 并格式化。
  4. 可在商品明细下方追加"再次购买"按钮，遍历 $order->items 向购物车批量加货。
================================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-checkout-success')

@section('content')

  <x-front-breadcrumb type="static" value="{{ front_route('orders.pay', ['number'=>$order->number]) }}"
                      title="{{ $order->number }}"/>

  @hookinsert('order.show.top')

  <div class="container">
    <div class="row">
      <div class="account-card-box order-info-box">
        <div class="account-card-title d-flex justify-content-between align-items-center">
          <span class="fw-bold">{{ __('front/order.order_details') }}</span>
        </div>
        <table class="table table-bordered table-striped mb-3 table-response">
          <thead>
          <tr>
            <th>{{ __('front/order.order_number') }}</th>
            <th>{{ __('front/order.order_date') }}</th>
            <th>{{ __('front/order.order_total') }}</th>
            <th>{{ __('front/order.order_status') }}</th>
            <th>{{ __('front/checkout.order_comment') }}</th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td data-title="Order ID">{{ $order->number }}</td>
            <td data-title="Order Date">{{ $order->created_at->format('Y-m-d') }}</td>
            <td data-title="Order Total">{{ $order->total_format }}</td>
            <td data-title="Order Status">{{ $order->status_format }}</td>
            <td data-title="Order comment">
              <span class="d-inline-block" tabindex="0" data-bs-toggle="tooltip"
                    data-bs-title="{{ sub_string($order->comment, 380) }}">
                {{ sub_string($order->comment) }}
              </span>
            </td>
          </tr>
          </tbody>
        </table>
        <div class="products-table mb-4">
          <table class="table products-table align-middle">
            <thead>
            <tr>
              <th>{{ __('front/order.product') }}</th>
              <th>{{ __('front/order.price') }}</th>
              <th>{{ __('front/order.quantity') }}</th>
              <th>{{ __('front/order.subtotal') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($order->items as $product)
              <tr>
                <td>
                  <div class="product-item">
                    <div class="product-image">
                      <img src="{{ $product['image'] }}" class="img-fluid">
                    </div>
                    <div class="product-info">
                      <div class="name">{{ $product['name'] }}</div>
                      <div class="sku mt-2 text-secondary">{{ $product['product_sku'] }}
                        @if ($product['variant_label'])
                          - {{ $product['variant_label'] }}
                        @endif
                      </div>
                      @if (!empty($product['options']))
                        <div class="product-options mt-2">
                          @foreach ($product['options'] as $option)
                            <div class="option-item text-muted small">
                              <strong>{{ $option['option_name'] }}:</strong> {{ $option['option_value_name'] }}
                              @if ($option['price_adjustment'] != 0)
                                <span class="text-primary">({{ $option['price_adjustment'] > 0 ? '+' : '' }}{{ currency_format($option['price_adjustment']) }})</span>
                              @endif
                            </div>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  </div>
                </td>
                <td>{{ $product['price_format'] }}</td>
                <td>{{ $product['quantity'] }}</td>
                <td>{{ $product['price_format'] }}</td>
              </tr>
            @endforeach

            @foreach ($order->fees as $total)
              <tr>
                <td></td>
                <td></td>
                <td><strong>{{ $total['title'] }}</strong></td>
                <td>{{ $total->value_format }}</td>
              </tr>
            @endforeach
            <tr>
              <td></td>
              <td></td>
              <td><strong>{{ __('front/order.order_total') }}</strong></td>
              <td>{{ $order->total_format }}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    @hookinsert('order.show.top')

    @endsection

    @push('footer')
      <script>

      </script>
  @endpush
