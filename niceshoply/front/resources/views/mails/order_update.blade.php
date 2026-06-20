{{--
  ============================================================
  【文件说明】
    订单状态更新通知邮件模板（发送给买家）。
    当商家在后台修改订单状态（如已发货、已完成、已取消等）时，
    系统自动向买家发送此邮件，提醒其订单有最新动态。
    邮件内容与 order_new.blade.php 结构相同，包含：
      1. 订单状态更新标题
      2. 称呼买家姓名
      3. 订单基本信息表格（订单号、下单时间、最新状态、订单总额）
      4. 订单商品明细表格（商品图片、名称、数量、小计）
      5. 费用汇总表格
      6. 「查看订单」按钮
      7. 签名（站点名称）

  【触发场景】
    后台管理员修改订单状态，且该状态配置了「发送邮件通知」选项时触发。
    典型场景：
      - 订单审核通过 → 发送「已确认」通知
      - 订单已发货   → 发送「已发货」通知（可在邮件中加快递信息）
      - 订单已完成   → 发送「已完成」通知
      - 订单已取消   → 发送「已取消」通知

  【对应触发方式】
    Mailable 类：NiceShoply\...\Mail\OrderUpdateMail（或类似命名）
    发送时机：Mail::to($order->email)->send(new OrderUpdateMail($order))

  【可用变量】
    $order — Order 模型  订单对象，主要属性（同 order_new.blade.php）：
               - number           订单号
               - customer_name    收货人姓名
               - email            买家邮箱
               - created_at       下单时间
               - status_format    最新订单状态文字（已格式化）
               - total            订单总金额
               - currency_code    货币代码
               - currency_value   货币汇率值
               - items            订单商品集合（image、name、quantity、price）
               - fees             费用项集合（title、value）

  【辅助函数】
    image_origin($path)                              — 生成商品图片完整 URL
    currency_format($amount, $code, $value)          — 格式化货币金额
    account_route('orders.number_show', [...])       — 生成订单详情页 URL

  【布局继承】
    @extends('layouts.mail') — 邮件基础布局
    @section('content')      — 填充邮件正文内容区

  【语言文案】
    front/mail.order_update  — 邮件标题（订单状态已更新）
    其余同 order_new，参见 resources/lang/{locale}/front/mail.php
    和 resources/lang/{locale}/front/order.php

  【与 order_new 的区别】
    仅标题文案不同（order_update vs order_success），
    其余内容结构完全相同，如需差异化（如加入物流单号），可在此处扩展。

  【自定义建议】
    - 发货通知时可增加快递公司名称和运单号字段（需在 Order 模型中扩展）
    - 可根据 $order->status 做条件判断，为不同状态显示不同的提示语或 CTA
    - 取消订单通知中可增加退款说明
    - 建议为「已发货」状态邮件单独建立模板（order_shipped.blade.php），内容更精准
  ============================================================
--}}
@extends('layouts.mail')

@section('content')
  <tbody>
  <tr style="font-weight:300">
    <td style="width:3.2%;max-width:30px;"></td>
    <td style="max-width:480px;text-align:left;">
      <h1 style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">
        {{ __('front/mail.order_update') }}
      </h1>
      <p style="font-size:14px;color:#333; line-height:24px; margin:0;">
        {{ __('front/mail.customer_name', ['name' => $order->customer_name]) }}
      </p>
      <p style="font-size: 13px;font-weight:bold;margin-bottom:6px;color: #333;">{{ __('front/order.order_details') }}
        :</p>
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
