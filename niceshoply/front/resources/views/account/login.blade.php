{{--
  ============================================================
  【文件说明】
    会员登录页。
    支持三种认证模式：仅邮箱登录（email_only）、仅手机号登录（phone_only）、
    两者兼支持（both）。
    支持 iframe 内嵌模式（?iframe=true），登录成功后关闭弹窗并刷新父页面。

  【访问权限】
    无需登录（公开访问）。
    已登录用户通常会被重定向至用户中心首页。

  【对应路由/控制器】
    路由名称（GET）：login.index
    路由名称（POST）：login.store
    URL 示例：/{locale}/login
    控制器：Front\Auth\LoginController@index / @store
    短信验证码发送：front_route('login.sms-code')（POST）

  【可用变量】
    $authMethod — string，认证方式，取值：
                    'email_only'  仅显示邮箱/密码表单
                    'phone_only'  仅显示手机号/验证码表单
                    'both'        两种方式均显示，前端切换

  【Sections】
    body-class → 'page-login'
    content    → 登录表单区域
    footer     → 表单提交及短信倒计时 JS 逻辑（@push）

  【插件钩子】
    @hookinsert('account.login.top')    — 容器顶部（面包屑下方）
    @hookinsert('account.login.bottom') — 容器底部

  【子视图引用】
    account/_social — 第三方社交登录按钮（如微信、Google、GitHub）

  【自定义建议】
    - 若要在登录框上方显示促销提示，在 account.login.top 钩子中插入
    - 若要自定义"忘记密码"链接样式，直接修改 <a href="{{ front_route('forgotten.index') }}"> 部分
    - 手机登录倒计时秒数（60s）在 @push('footer') JS 中可调整
    - iframe 模式下面包屑和"忘记密码"链接会自动隐藏（request('iframe') 判断）
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-login')

@section('content')
  @if (!request('iframe'))
    <x-front-breadcrumb type="route" value="login.index" title="{{ __('front/account.login') }}"/>
  @endif

  @hookinsert('account.login.top')

  <div class="container">
    <div class="login-register-box {{ request('iframe') ? 'iframe' : '' }}">
      <div class="login-title">{{ __('front/login.login') }}</div>
      <div class="login-sub-title">{{ __('front/login.login_text') }}</div>
      
      @if($authMethod === 'both')
        <div class="auth-method-switch mb-3">
          <div class="btn-group w-100" role="group">
            <button type="button" class="btn btn-outline-primary active" data-method="email">
              {{ __('front/login.login_by_email') }}
            </button>
            <button type="button" class="btn btn-outline-primary" data-method="phone">
              {{ __('front/login.login_by_phone') }}
            </button>
          </div>
        </div>
      @endif

      <form action="{{ front_route('login.store') }}" class="needs-validation form-wrap" novalidate>
        @csrf
        
        @if($authMethod === 'email_only' || $authMethod === 'both')
          <div class="auth-form auth-form-email" @if($authMethod === 'both') style="display: none;" @endif>
            <div class="form-group mb-4">
              <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                     value="{{ old('email') }}" 
                     @if($authMethod === 'email_only') required @elseif($authMethod === 'both') data-required-with="email" @endif 
                     autocomplete="email" placeholder="{{ __('front/login.email') }}"/>
              <span class="invalid-feedback" role="alert"><strong>{{ __('front/login.email_required') }}</strong></span>
            </div>

            <div class="form-group mb-4">
              <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                     name="password" 
                     @if($authMethod === 'email_only') required @elseif($authMethod === 'both') data-required-with="email" @endif 
                     autocomplete="current-password" placeholder="{{ __('front/login.password') }}"/>
              <span class="invalid-feedback" role="alert"><strong>{{ __('front/login.password_required') }}</strong></span>
            </div>
            @if (!request('iframe'))
              <a href="{{ front_route('forgotten.index') }}" class="text-secondary mt-n2 d-block">{{
            __('front/login.forget_password') }} <i class="bi bi-question-circle"></i></a>
            @endif
          </div>
        @endif

        @if($authMethod === 'phone_only' || $authMethod === 'both')
          <div class="auth-form auth-form-phone" @if($authMethod === 'both') style="display: none;" @endif>
            <div class="form-group mb-4">
              <div class="row">
                <div class="col-4">
                  <input type="text" class="form-control" name="calling_code" 
                         @if($authMethod === 'phone_only') required @elseif($authMethod === 'both') data-required-with="phone" @endif 
                         placeholder="{{ __('front/login.calling_code') }}" value="{{ old('calling_code', '+86') }}" />
                </div>
                <div class="col-8">
                  <input type="tel" class="form-control" name="telephone" 
                         @if($authMethod === 'phone_only') required @elseif($authMethod === 'both') data-required-with="phone" @endif 
                         placeholder="{{ __('front/login.telephone') }}" value="{{ old('telephone') }}" />
                </div>
              </div>
            </div>
            <div class="form-group mb-4">
              <div class="input-group">
                <input type="text" class="form-control" name="code" 
                       @if($authMethod === 'phone_only') required @elseif($authMethod === 'both') data-required-with="phone" @endif 
                       placeholder="{{ __('front/login.sms_code') }}" maxlength="6" />
                <button type="button" class="btn btn-outline-secondary" id="send-sms-code"
                        @if($authMethod === 'both') data-required-with="phone" @endif>
                  {{ __('front/login.send_code') }}
                </button>
              </div>
              <span class="invalid-feedback" role="alert"><strong>{{ __('front/login.code_required') }}</strong></span>
            </div>
          </div>
        @endif

        <div class="btn-submit">
          <button type="button" class="btn btn-primary form-submit btn-lg">{{ __('front/login.login_submit') }}</button>
          <a href="{{ front_route('register.index') }}{{ request('iframe') ? '?iframe=true' : '' }}">{{
          __('front/login.no_account') }}
            <i class="bi bi-arrow-up-right-square"></i></a>
        </div>
      </form>

      @include('account/_social')

    </div>
  </div>

  @hookinsert('account.login.bottom')

@endsection

@push('footer')
  <script>
    const iframe = @json(request('iframe', false));
    const authMethod = @json($authMethod);

    @if($authMethod === 'both')
      // Switch between email and phone login
      $('.auth-method-switch button').on('click', function() {
        const method = $(this).data('method');
        $('.auth-method-switch button').removeClass('active');
        $(this).addClass('active');
        
        $('.auth-form').hide();
        $('.auth-form-' + method).show();
        
        // Update required attributes
        $('.auth-form-' + method + ' [data-required-with]').attr('required', true);
        $('.auth-form').not('.auth-form-' + method).find('[data-required-with]').removeAttr('required');
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
        layer.msg('{{ __('front/login.please_enter_phone') }}', {icon: 2});
        return;
      }
      
      const btn = $(this);
      btn.prop('disabled', true);
      btn.text('{{ __('front/login.sending') }}...');
      
      axios.post('{{ front_route('login.sms-code') }}', {
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
              btn.text('{{ __('front/login.send_code') }}');
            }
          }, 1000);
        } else {
          layer.msg(res.message, {icon: 2});
          btn.prop('disabled', false);
          btn.text('{{ __('front/login.send_code') }}');
        }
      }).catch(function() {
        btn.prop('disabled', false);
        btn.text('{{ __('front/login.send_code') }}');
      });
    });

    inno.validateAndSubmitForm('.form-wrap', function (data) {
      layer.load(2, {shade: [0.3, '#fff']})
      
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
        }
      }
      
      axios.post($('.form-wrap').attr('action'), data).then(function (res) {
        if (res.success) {
          if (iframe) {
            setTimeout(() => {
              parent.layer.closeAll()
              parent.window.location.reload()
            }, 400);
          } else {
            layer.msg(res.message, {icon: 1})
            if (res.data.redirect_uri) {
              location.href = res.data.redirect_uri;
            } else {
              location.href = '{{ front_route('account.index') }}';
            }
          }
        } else {
          layer.msg(res.message, {icon: 2});
        }
      }).finally(function () {
        layer.closeAll('loading')
      });
    });
  </script>
@endpush
