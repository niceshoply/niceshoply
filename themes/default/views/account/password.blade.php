{{--
  ============================================================
  【文件说明】
    用户中心 — 修改密码页。
    已登录会员通过输入旧密码 + 新密码 + 确认密码来更新账户密码。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET） ：account.password.index
    路由名称（PUT） ：account.password.update
    URL 示例：/{locale}/account/password
    控制器：Front\Account\PasswordController@index / @update
    表单方法：POST + @method('PUT')

  【可用变量】
    本页面无需控制器额外注入变量，session 数据：
    session('success') — 修改成功提示文本（string）
    session('errors')  — 验证错误集合（通过 session()->has('errors') 判断）

  【Sections】
    body-class → 'page-edit'
    content    → 修改密码表单（含旧密码/新密码/确认密码三个字段）
    footer     → 预留空 <script>（@push）

  【插件钩子】
    @hookinsert('account.password.top')    — 容器顶部（侧边栏上方）
    @hookinsert('account.password.bottom') — 容器底部

  【表单字段】
    old_password              — 旧密码（required）
    new_password              — 新密码（required）
    new_password_confirmation — 确认新密码（required）

  【自定义建议】
    - 可在表单下方添加密码强度提示组件
    - 若要支持无密码账户（社交登录用户）首次设置密码，可隐藏 old_password 字段
      并在控制器中跳过旧密码校验
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-edit')

@section('content')
  <x-front-breadcrumb type="route" value="account.password.index" title="{{ __('front/account.password') }}"/>

  @hookinsert('account.password.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box addresses-box">
          @if (session()->has('errors'))
            <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4"/>
          @endif
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4" />
          @endif

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/password.password') }} </span>
          </div>

          <form action="{{ account_route('password.update') }}" class="needs-validation" novalidate method="POST">
            @csrf
            @method('PUT')

            <x-common-form-input name="old_password" title="{{ __('front/password.old_password') }}" value="" type="password" required="required" placeholder="{{ __('front/password.old_password') }}" />
            <x-common-form-input name="new_password" title="{{ __('front/password.new_password') }}" value="" type="password" required="required" placeholder="{{ __('front/password.new_password') }}" />
            <x-common-form-input name="new_password_confirmation" title="{{ __('front/password.confirm_password') }}" value="" type="password" required="required" placeholder="{{ __('front/password.confirm_password') }}" />

            <button type="submit" class="btn btn-primary btn-lg mt-4 submit-form w-50">{{ __('front/common.submit') }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.password.bottom')

@endsection

@push('footer')
  <script></script>
@endpush