{{--
  ============================================================
  【文件说明】
    新订单确认邮件模板（发送给买家）。
    订单提交成功后，系统自动向买家发送此邮件，内容包含：
      1. 订单成功提交标题
      2. 称呼买家姓名
      3. 订单基本信息表格（订单号、下单时间、订单状态、订单总额）
      4. 订单商品明细表格（商品图片 60×60、商品名、数量、小计）
      5. 费用汇总表格（含运费、折扣、总计等各费用项）
      6. 「查看订单」按钮（跳转至订单详情页，支持游客通过 email 查询）
      7. 签名（站点名称）

  【触发场景】
    买家在前台完成下单（支付或提交订单）后触发。
    由 OrderController 或 OrderObserver 在创建订单成功事件中发送。

  【对应触发方式】
    Mailable 类：NiceShoply\...\Mail\OrderNewMail（或类似命名）
    发送时机：Mail::to($order->email)->send(new OrderNewMail($order))

  【可用变量】
    $order — Order 模型  订单对象，主要属性：
               - number           订单号（字符串）
               - customer_name    收货人姓名（用于称呼）
               - email            买家邮箱
               - created_at       下单时间（Carbon 实例）
               - status_format    订单状态文字（已格式化）
               - total            订单总金额（数值）
               - currency_code    货币代码（如 CNY、USD）
               - currency_value   货币汇率值
               - items            订单商品集合，每条含：
                                    - image     商品图片路径（用 image_origin() 生成 URL）
                                    - name      商品名称
                                    - quantity  购买数量
                                    - price     商品单价（小计）
               - fees             费用项集合，每条含：
                                    - title     费用名称（如运费、优惠券）
                                    - value     费用金额

  【辅助函数】
    image_origin($path)                                    — 生成商品图片完整 URL
    currency_format($amount, $code, $value)                — 格式化货币金额（含货币符号）
    account_route('orders.number_show', [...])             — 生成订单详情页 URL（支持游客访问）

  【布局继承】
    @extends('layouts.mail') — 邮件基础布局
    @section('content')      — 填充邮件正文内容区

  【语言文案】
    front/mail.order_success    — 邮件标题（订单提交成功）
    front/mail.customer_name    — 称呼模板（:name 占位符）
    front/order.order_details   — 表格标题（订单详情）
    front/order.order_number    — 列头（订单号）
    front/order.order_date      — 列头（下单日期）
    front/order.order_status    — 列头（订单状态）
    front/order.order_total     — 列头（订单总额）
    front/order.order_items     — 商品明细标题
    front/order.image           — 列头（商品图片）
    front/order.product         — 列头（商品名称）
    front/order.quantity        — 列头（数量）
    front/order.subtotal        — 列头（小计）
    front/common.view           — 按钮文字（查看）

  【自定义建议】
    - 可在商品明细中增加规格/属性显示（$product->sku_name 等）
    - 可在费用汇总下方增加预计配送时间说明
    - 可增加售后服务联系方式或退换货政策链接
    - 若需同时通知商家，可在 Mailable 中 CC 或单独发送另一封邮件
  ============================================================
--}}
@extends('layouts.mail')

@section('content')
  <tbody>
  <tr style="font-weight:300">
    <td style="width:3.2%;max-width:30px;"></td>
    <td style="max-width:480px;text-align:left;">
      <h1 style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">
        {{ __('front/mail.order_success') }}
      </h1>
      <p style="font-size:14px;color:#333; line-height:24px; margin:0;">
        {{ __('front/mail.customer_name', ['name' => $order->customer_name]) }}
      </p>
      <p style="font-size: 13px;font-weight:bold;margin-bottom:6px;color: #333;">{{ __('front/order.order_details') }}
        ：</p>
      <table
          style="width:100%;font-weight:300;margin-top:10px; margin-bottom:10px;border-collapse:collapse; background-color:#f8f9fa">
        <thead>
        <tr>
          <td style="font-size:13px;padding: 7px 6px">{{ __('front/order.order_number') }}</td>
          <td style="font-size:13px;padding: 7px 6px">{{ __('front/order.order_date') }}</td>
          <td style="font-size:13px;padding: 7px 6px">{{ __('front/order.order_status') }}</td>
          <td style="font-size:13px;padding: 7px 6px">{{ __('front/order.order_total') }}</td>
        </tr>
        </thead>
        <tbody>
        <tr>
          <td style="padding:7px;font-size:13px;">{{ $order->number }}</td>
          <td style="padding:7px;font-size:13px;">{{ $order->created_at }}</td>
          <td style="padding:7px;font-size:13px;">
            {{ $order->status_format }}
          </td>
          <td style="padding:7px;font-size:13px;">{{ currency_format($order->total, $order->currency_code, $order->currency_value) }}</td>
        </tr>
        </tbody>
      </table>

      <p style="font-size: 13px;font-weight:bold;margin-bottom:6px;color: #333;">{{ __('front/order.order_items') }}
        ：</p>
      <table style="width:100%;font-weight:300;margin-top:10px; margin-bottom:10px;border-collapse:collapse; ">
        <thead>
        <tr>
          <td style="font-size:13px;border: 1px solid #eee; background-color: #f8f9fa;padding: 7px 4px;width: 80px;text-align:center">{{ __('front/order.image') }}</td>
          <td style="font-size:13px;border: 1px solid #eee; background-color: #f8f9fa;padding: 7px 4px">{{ __('front/order.product') }}</td>
          <td style="font-size:13px;border: 1px solid #eee; background-color: #f8f9fa;padding: 7px 4px">{{ __('front/order.quantity') }}</td>
          <td style="font-size:13px;border: 1px solid #eee; background-color: #f8f9fa;padding: 7px 4px">{{ __('front/order.subtotal') }}</td>
        </tr>
        </thead>
        <tbody>
        @foreach ($order->items as $product)
          <tr>
            <td style="border: 1px solid #eee;padding:4px;text-align:center"><img style="width: 60px; height: 60px;"
                                                                                  src="{{ image_origin($product->image) }}">
            </td>
            <td style="font-size:12px; border: 1px solid #eee; width: 50%;padding:4px;">{{ $product->name }}</td>
            <td style="border: 1px solid #eee;padding:4px;font-size: 13px;">{{ $product->quantity }}</td>
            <td style="border: 1px solid #eee;padding:4px;font-size: 13px;">{{ currency_format($product->price, $order->currency_code, $order->currency_value) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>

      <p style="font-size: 13px;font-weight:bold;margin-bottom:6px;color: #333;">{{ __('front/order.order_total') }}
        ：</p>
      <table
          style="width:100%;font-weight:300;margin-top:10px; margin-bottom:10px;border-collapse:collapse;border:1px solid #eee;">
        <tbody>
        @foreach ($order->fees as $total)
          <tr>
            <td style="border: 1px solid #eee;padding:4px; background-color: #f8f9fa;font-size:13px;padding: 7px;width: 30%">{{ $total->title }}</td>
            <td style="border: 1px solid #eee;padding:4px;font-size:13px;padding: 7px">
              <strong>{{ currency_format($total->value, $order->currency_code, $order->currency_value) }}</strong></td>
          </tr>
        @endforeach
        </tbody>
      </table>

      <p style="font-size: 14px; color: rgb(51, 51, 51); line-height: 24px; margin: 6px 0px 0px; word-wrap: break-word; word-break: break-all;">
        <a href="{{ account_route('orders.number_show', ['number' => $order->number, 'email' => $order->email]) }}" title=""
           style="font-size: 16px; line-height: 45px; display: block; background-color: #944FE8; color: rgb(255, 255, 255); text-align: center; text-decoration: none; margin-top: 20px; border-radius: 3px;">
          {{ __('front/common.view') }}
        </a>
      </p>

      <dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;">
        <dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;">
          <p style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">
            <br>
            <strong>{{ config('app.name') }}</strong>
            </p>
          </dd>
        </dl>

      </td>
      <td style="width:3.2%;max-width:30px;"></td>
    </tr>
  </tbody>
@endsection
