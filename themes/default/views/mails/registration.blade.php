{{--
  ============================================================
  【文件说明】
    会员注册欢迎邮件模板。
    用户完成注册后，系统自动发送此邮件，内容包含：
      1. 欢迎标题（欢迎加入 + 站点名称）
      2. 称呼会员姓名
      3. 注册成功提示文字
      4. 「立即购物」按钮（跳转至首页）
      5. 签名（站点名称）

  【触发场景】
    用户在前台完成注册（邮箱 + 密码 / 第三方登录注册）后触发。
    由 RegisterController 或 CustomerObserver 在注册成功事件中发送。

  【对应触发方式】
    Mailable 类：NiceShoply\...\Mail\RegistrationMail（或类似命名）
    发送时机：注册成功后 Mail::to($customer->email)->send(new RegistrationMail($customer))

  【可用变量】
    $customer — Customer 模型  新注册会员对象，主要属性：
                  - name   会员姓名（用于邮件称呼）
                  - email  会员邮箱

  【辅助函数】
    config('app.url')  — 站点首页 URL（「立即购物」按钮跳转地址）
    config('app.name') — 站点名称（邮件签名用）

  【布局继承】
    @extends('layouts.mail') — 邮件基础布局（提供邮件 HTML 框架、Logo、页脚等）
    @section('content')      — 填充邮件正文内容区（<tbody> 行内容）

  【语言文案】
    front/mail.welcome_register — 欢迎标题文字
    front/mail.customer_name    — 称呼模板（:name 占位符）
    front/mail.register_end     — 注册成功说明文字
    front/mail.btn_buy_now      — 按钮文字（立即购物）
    文案文件位于：resources/lang/{locale}/front/mail.php

  【自定义建议】
    - 可在邮件中增加新用户专属优惠券或首单折扣说明
    - 可增加常用功能快捷入口（如订单查询、个人中心链接）
    - 可在欢迎语下方加入平台特色介绍（品牌故事/服务承诺）
    - 若需发送 HTML 富文本邮件，可在 layouts.mail 中扩展模板
  ============================================================
--}}
@extends('layouts.mail')

@section('content')
  <tbody>
  <tr style="font-weight:300">
    <td style="width:3.2%;max-width:30px;"></td>
    <td style="max-width:480px;text-align:left;">
      <h1 style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">
        {{ __('front/mail.welcome_register') }} {{ config('app.name') }}
      </h1>
      <p style="font-size:14px;color:#333; line-height:24px; margin:0;">
        {{ __('front/mail.customer_name', ['name' => $customer->name]) }}
      </p>
      <p style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;">
        <span style="color: rgb(51, 51, 51); font-size: 14px;">{{ __('front/mail.register_end') }}</span>
      </p>
      <p style="font-size: 14px; color: rgb(51, 51, 51); line-height: 24px; margin: 6px 0px 0px; word-wrap: break-word; word-break: break-all;">
        <a href="{{ config('app.url') }}" title=""
           style="font-size: 16px; line-height: 45px; display: block; background-color: #944FE8; color: rgb(255, 255, 255); text-align: center; text-decoration: none; margin-top: 20px; border-radius: 3px;">
          {{ __('front/mail.btn_buy_now') }}
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
