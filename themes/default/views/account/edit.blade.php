{{--
  ============================================================
  【文件说明】
    用户中心 — 编辑个人资料页。
    支持修改头像、昵称、邮箱，以及绑定/更新手机号（含短信验证码验证）。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET） ：account.edit.index
    路由名称（PUT） ：account.edit.index（同路由，RESTful）
    短信验证码（POST）：account.edit.sms-code
    URL 示例：/{locale}/account/edit
    控制器：Front\Account\EditController@index / @update / @smsCode
    表单方法：POST + @method('PUT')

  【可用变量】
    $customer — 当前登录会员对象（Model），模板中使用的字段：
                  $customer->avatar       头像路径（传给 x-common-form-imagep 组件）
                  $customer->name         昵称/姓名
                  $customer->email        邮箱地址
                  $customer->calling_code 电话区号（如 '+86'）
                  $customer->telephone    手机号码

  【Sections】
    body-class → 'page-edit'
    content    → 个人资料表单
    footer     → 发送短信验证码倒计时 JS（@push）

  【插件钩子】
    @hookinsert('account.edit.top')    — 容器顶部
    @hookinsert('account.edit.bottom') — 容器底部

  【表单字段】
    avatar        — 头像（图片上传组件 x-common-form-imagep）
    name          — 昵称（required）
    email         — 邮箱（required）
    calling_code  — 电话区号（非必填，默认 +86）
    telephone     — 手机号（非必填，更新时需短信验证码）
    code          — 短信验证码（仅更换手机号时需要）

  【自定义建议】
    - 如需添加"生日""性别"等字段，直接在表单中增加对应 input 并在控制器更新逻辑中处理
    - 头像上传使用 x-common-form-imagep 组件，该组件封装了图片预览和上传接口调用
    - 手机号短信倒计时为 60 秒，可在 @push('footer') JS 中调整
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-edit')

@section('content')
  <x-front-breadcrumb type="route" value="account.edit.index" title="{{ __('front/account.edit') }}"/>

  @hookinsert('account.edit.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box addresses-box">
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-4"/>
          @endif

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/edit.edit') }} </span>
          </div>

          <form class="needs-validation edit-form" action="{{ account_route('edit.index') }}" method="POST" novalidate>
            @csrf
            @method('PUT')

            <x-common-form-imagep name="avatar" title="{{ __('front/edit.avatar') }}"
                                 value="{{ old('avatar', $customer->avatar) }}"/>
            <x-common-form-input name="name" title="{{ __('front/edit.name') }}"
                                 value="{{ old('name', $customer->name) }}" required="required"
                                 placeholder="{{ __('front/edit.name') }}"/>
            <x-common-form-input name="email" title="{{ __('front/edit.email') }}"
                                 value="{{ old('email', $customer->email) }}" required="required"
                                 placeholder="{{ __('front/edit.email') }}"/>

            <div class="form-group mb-4">
              <label class="form-label">{{ __('front/edit.telephone') }}</label>
              <div class="row mb-3">
                <div class="col-4">
                  <input type="text" class="form-control" name="calling_code" 
                         placeholder="{{ __('front/edit.calling_code') }}" 
                         value="{{ old('calling_code', $customer->calling_code ?? '+86') }}" />
                </div>
                <div class="col-8">
                  <input type="tel" class="form-control" name="telephone" 
                         placeholder="{{ __('front/edit.telephone') }}" 
                         value="{{ old('telephone', $customer->telephone) }}" />
                </div>
              </div>
              <div class="input-group mb-3">
                <input type="text" class="form-control" name="code" 
                       placeholder="{{ __('front/edit.sms_code') }}" maxlength="6" />
                <button type="button" class="btn btn-outline-secondary" id="send-sms-code">
                  {{ __('front/edit.send_code') }}
                </button>
              </div>
              <div class="text-secondary"><small>{{ __('front/edit.phone_update_hint') }}</small></div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-50">{{ __('front/common.submit') }}</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.edit.bottom')

@endsection

@push('footer')
<script>
  // Send SMS code for phone number update
  $('#send-sms-code').on('click', function() {
    const callingCode = $('input[name="calling_code"]').val();
    const telephone = $('input[name="telephone"]').val();
    
    if (!callingCode || !telephone) {
      layer.msg('{{ __('front/edit.please_enter_phone') }}', {icon: 2});
      return;
    }
    
    const btn = $(this);
    btn.prop('disabled', true);
    btn.text('{{ __('front/edit.sending') }}...');
    
    axios.post('{{ account_route('edit.sms-code') }}', {
      calling_code: callingCode,
      telephone: telephone,
      _token: '{{ csrf_token() }}'
    }).then(function(res) {
      if (res.success) {
        layer.msg(res.message, {icon: 1});
        // Start countdown
        let countdown = 60;
        const timer = setInterval(function() {
          btn.text(countdown + 's');
          countdown--;
          if (countdown < 0) {
            clearInterval(timer);
            btn.prop('disabled', false);
            btn.text('{{ __('front/edit.send_code') }}');
          }
        }, 1000);
      } else {
        layer.msg(res.message, {icon: 2});
        btn.prop('disabled', false);
        btn.text('{{ __('front/edit.send_code') }}');
      }
    }).catch(function(error) {
      const message = error.response?.data?.message || '{{ __('front/edit.send_code_failed') }}';
      layer.msg(message, {icon: 2});
      btn.prop('disabled', false);
      btn.text('{{ __('front/edit.send_code') }}');
    });
  });
</script>
@endpush
