{{--
  ============================================================
  【文件说明】
    密码找回邮件模板。
    发送给请求重置密码的用户，邮件内容包含：
      1. 标题（找回密码）
      2. 说明文字
      3. 验证码（粗体显示）
      4. 一键重置密码按钮（带验证码和邮箱参数的链接）
      5. 签名（站点名称）

  【触发场景】
    用户在前台「忘记密码」页面提交邮箱，系统向该邮箱发送此邮件。
    管理员在后台找回密码时，链接同样指向后台找回页面（is_admin = true）。

  【对应触发方式】
    Mailable 类：NiceShoply\...\Mail\ForgottenMail（或类似命名）
    发送时机：ForgottenController@send 调用 Mail::to($email)->send(new ForgottenMail(...))

  【可用变量】
    $code       — string   6 位验证码（直接输出，需加粗显示）
    $email      — string   用户邮箱地址（用于构造重置链接的 email 参数）
    $is_admin   — bool     是否为后台管理员找回密码，true 时链接跳转后台路由

  【辅助函数】
    front_route('forgotten.index')   — 前台找回密码页 URL（带语言前缀）
    console_route('forgotten.index') — 后台找回密码页 URL

  【布局继承】
    @extends('layouts.mail') — 邮件基础布局（提供邮件 HTML 框架、Logo、页脚等）
    @section('content')      — 填充邮件正文内容区（<tbody> 行内容）

  【自定义建议】
    - 可在说明文字下方增加验证码有效期提示（如「验证码 30 分钟内有效」）
    - 按钮颜色 #944FE8 可修改为品牌主色（在 layouts.mail 中统一定义变量更佳）
    - 可增加防盗用免责声明：「如果您未发起此操作，请忽略此邮件」
    - 语言文案在 resources/lang/zh/front/mail.php 中定义
  ============================================================
--}}
@extends('layouts.mail')

@section('content')
  <tbody>
  <tr style="font-weight:300">
    <td style="width:3.2%;max-width:30px;"></td>
    <td style="max-width:480px;text-align:left;">
      <h1 style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">
        {{ __('front/mail.retrieve_password_title') }}
      </h1>
      <p style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;">
          <span style="color: rgb(51, 51, 51); font-size: 14px;">{{ __('front/mail.retrieve_password_text') }}
          </span>
      </p>
      <p style="line-height: 24px; margin: 6px 0px 0px; overflow-wrap: break-word; word-break: break-all;">
          <span style="color: rgb(51, 51, 51); font-size: 14px;">{{ __('front/forgotten.verification_code') }}:
            <span style="font-weight: bold;">{{ $code }}</span>
          </span>
      </p>
      <p style="font-size: 14px; color: rgb(51, 51, 51); line-height: 24px; margin: 6px 0px 0px; word-wrap: break-word; word-break: break-all;">
        <a href="{{ $is_admin ? console_route('forgotten.index') : front_route('forgotten.index') }}?code={{ $code }}&email={{ $email }}"
           title=""
           style="font-size: 16px; line-height: 45px; display: block; background-color: #944FE8; color: rgb(255, 255, 255); text-align: center; text-decoration: none; margin-top: 20px; border-radius: 3px;">
          {{ __('front/mail.retrieve_password_btn') }}
        </a>
      </p>
      <dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;">
        <dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;">
          <p style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">
            <strong>{{ config('app.name') }}</strong>
            </p>
          </dd>
        </dl>
      </td>
      <td style="width:3.2%;max-width:30px;"></td>
    </tr>
  </tbody>
@endsection