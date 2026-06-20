{{--
  ============================================================
  【文件说明】
    会员注册页。
    支持三种注册模式：仅邮箱注册（email_only）、仅手机号注册（phone_only）、
    两者兼支持（both）。
    支持 iframe 内嵌模式（?iframe=true），注册成功后关闭弹窗并刷新父页面。

  【访问权限】
    无需登录（公开访问）。

  【对应路由/控制器】
    路由名称（GET）：register.index
    路由名称（POST）：register.store
    URL 示例：/{locale}/register
    控制器：Front\Auth\RegisterController@index / @store
    短信验证码发送：front_route('register.sms-code')（POST）

  【可用变量】
    $authMethod — string，注册方式，取值：
                    'email_only'  仅显示邮箱/密码表单
                    'phone_only'  仅显示手机号/验证码表单
                    'both'        两种方式均显示，前端切换

  【Sections】
    body-class → 'page-login'（与登录页共用同一页面样式类）
    content    → 注册表单区域
    footer     → 表单提交及短信倒计时 JS 逻辑（@push）

  【插件钩子】
    @hookinsert('account.register.top')         — 容器顶部（面包屑下方）
    @hookinsert('account.register.bottom')      — 容器底部
    @hookinsert('account.register.email.after') — 邮箱输入框后（可插入自定义字段，如昵称）
    @hookupdate('account.register.email')       — 邮箱输入框整体区域（可完全替换）

  【子视图引用】
    account/_social — 第三方社交登录/注册按钮

  【自定义建议】
    - 如需添加"昵称""性别"等额外注册字段，在 account.register.email.after 钩子中插入
    - password_confirmation 字段被设为 d-none（隐藏），JS 中自动同步值，无需用户二次输入；
      若要求用户手动确认密码，可移除 d-none 类并添加 required 属性
    - 注册成功后默认跳转至 front_route('account.index')，可在控制器中修改重定向目标
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-login')

@section('content')
@if (!request('iframe'))
  <x-front-breadcrumb type="route" value="register.index" title="{{ __('front/account.register') }}" />
@endif

@hookinsert('account.register.top')

<div class="container">
  <div class="login-register-box {{ request('iframe') ? 'iframe' : '' }}">
    <div class="login-title">{{ __('front/register.register') }}</div>
    <div class="login-sub-title">{{ __('front/register.register_text') }}</div>
    
    @if($authMethod === 'both')
      <div class="auth-method-switch mb-3">
        <div class="btn-group w-100" role="group">
          <button type="button" class="btn btn-outline-primary active" data-method="email">
            {{ __('front/register.register_by_email') }}
          </button>
          <button type="button" class="btn btn-outline-primary" data-method="phone">
            {{ __('front/register.register_by_phone') }}
          </button>
        </div>
      </div>
    @endif

    <form action="{{ front_route('register.store') }}" class="needs-validation form-wrap" novalidate>
      @csrf
      
      @if($authMethod === 'email_only' || $authMethod === 'both')
        <div class="auth-form auth-form-email" @if($authMethod === 'both') style="display: none;" @endif>
          @hookupdate('account.register.email')
          <div class="form-group mb-4">
            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" 
                   @if($authMethod === 'email_only') required @elseif($authMethod === 'both') data-required-with="email" @endif 
                   autocomplete="email" placeholder="{{ __('front/login.email') }}" />
            <span class="invalid-feedback" role="alert"><strong>{{ __('front/login.email_required') }}</strong></span>
          </div>
          @endhookupdate
          @hookinsert('account.register.email.after')
          <div class="form-group mb-4">
            <input id="password" type="password" class="form-control" name="password" 
                   @if($authMethod === 'email_only') required @elseif($authMethod === 'both') data-required-with="email" @endif 
                   autocomplete="new-password" placeholder="{{ __('front/login.password') }}" />
            <input id="password_confirmation" type="password" class="d-none" name="password_confirmation" 
                   autocomplete="new-password" />
            <span class="invalid-feedback" role="alert"><strong>{{ __('front/login.password_required') }}</strong></span>
          </div>
        </div>
      @endif

      @if($authMethod === 'phone_only' || $authMethod === 'both')
        <div class="auth-form auth-form-phone" @if($authMethod === 'both') style="display: none;" @endif>
          <div class="form-group mb-4">
            <div class="row">
              <div class="col-4">
                <input type="text" class="form-control" name="calling_code" 
                       @if($authMethod === 'phone_only') required @elseif($authMethod === 'both') data-required-with="phone" @endif 
                       placeholder="{{ __('front/register.calling_code') }}" value="{{ old('calling_code', '+86') }}" />
              </div>
              <div class="col-8">
                <input type="tel" class="form-control" name="telephone" 
                       @if($authMethod === 'phone_only') required @elseif($authMethod === 'both') data-required-with="phone" @endif 
                       placeholder="{{ __('front/register.telephone') }}" value="{{ old('telephone') }}" />
              </div>
            </div>
          </div>
          <div class="form-group mb-4">
            <div class="input-group">
              <input type="text" class="form-control" name="code" 
                     @if($authMethod === 'phone_only') required @elseif($authMethod === 'both') data-required-with="phone" @endif 
                     placeholder="{{ __('front/register.sms_code') }}" maxlength="6" />
              <button type="button" class="btn btn-outline-secondary" id="send-sms-code" 
                      @if($authMethod === 'both') data-required-with="phone" @endif>
                {{ __('front/register.send_code') }}
              </button>
            </div>
            <span class="invalid-feedback" role="alert"><strong>{{ __('front/register.code_required') }}</strong></span>
          </div>
        </div>
      @endif

      <div class="btn-submit">
        <button type="button" class="btn btn-primary form-submit btn-lg">{{ __('front/register.register_submit') }}</button>
        <a href="{{ front_route('login.index') }}{{ request('iframe') ? '?iframe=true' : '' }}">{{ __('front/register.have_account') }} <i class="bi bi-arrow-up-right-square"></i></a>
      </div>

      @include('account/_social')

    </form>
  </div>
</div>

@hookinsert('account.register.bottom')

@endsection

@push('footer')
<script>
  const iframe = @json(request('iframe', false));
  const authMethod = @json($authMethod);

  @if($authMethod === 'both')
    // Switch between email and phone registration
    $('.auth-method-switch button').on('click', function() {
      const method = $(this).data('method');
      $('.auth-method-switch button').removeClass('active');
      $(this).addClass('active');
      
      $('.auth-form').hide();
      $('.auth-form-' + method).show();
      
      // Update required attributes
      $('.auth-form-' + method + ' [data-required-with]').attr('required', true);
      $('.auth-form').not('.auth-form-' + method + ' [data-required-with]').removeAttr('required');
    });
    
    // Set default to email
    $('.auth-form-email').show();
    $('.auth-form-email [data-required-with="email"]').attr('required', true);
  @endif

  // Send SMS code
  $('#send-sms-code').on('click', function() {
    const callingCode = $('input[name="calling_code"]').val();
    const telephone = $('input[name="telephone"]').val();
    
    if (!callingCode || !telephone) {
      layer.msg('{{ __('front/register.please_enter_phone') }}', {icon: 2});
      return;
    }
    
    const btn = $(this);
    btn.prop('disabled', true);
    btn.text('{{ __('front/register.sending') }}...');
    
    axios.post('{{ front_route('register.sms-code') }}', {
      calling_code: callingCode,
      telephone: telephone
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
            btn.text('{{ __('front/register.send_code') }}');
          }
        }, 1000);
      } else {
        layer.msg(res.message, {icon: 2});
        btn.prop('disabled', false);
        btn.text('{{ __('front/register.send_code') }}');
      }
    }).catch(function() {
      btn.prop('disabled', false);
      btn.text('{{ __('front/register.send_code') }}');
    });
  });

  inno.validateAndSubmitForm('.form-wrap', function(serializedData) {
    layer.load(2, {shade: [0.3,'#fff'] })
    
    // Parse serialized data to object
    const data = {};
    serializedData.split('&').forEach(function(item) {
      const parts = item.split('=');
      if (parts.length === 2) {
        data[decodeURIComponent(parts[0])] = decodeURIComponent(parts[1]);
      }
    });
    
    // Ensure password_confirmation matches password
    if (data.password) {
      data.password_confirmation = data.password;
    }
    
    // Remove hidden fields based on auth method
    if (authMethod === 'both') {
      const activeMethod = $('.auth-method-switch button.active').data('method');
      if (activeMethod === 'email') {
        delete data.calling_code;
        delete data.telephone;
        delete data.code;
      } else {
        delete data.email;
        delete data.password;
        delete data.password_confirmation;
      }
    }
    
    const params = new URLSearchParams(data);

    axios.post($('.form-wrap').attr('action'), params.toString()).then(function(res) {
      if (res.success) {
        if (iframe) {
          setTimeout(() => {
            parent.layer.closeAll()
            parent.window.location.reload()
          }, 400);
        } else {
          layer.msg(res.message, {icon: 1})
          location.href = '{{ front_route('account.index') }}';
        }
      } else {
        layer.msg(res.message, { icon: 2 });
      }
    }).finally(function() {layer.closeAll('loading')});
  });
</script>
@endpush
