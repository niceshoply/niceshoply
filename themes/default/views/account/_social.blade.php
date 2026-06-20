{{--
  ============================================================
  【文件说明】
    第三方社交登录/注册按钮局部视图（partial）。
    读取系统设置中已启用的社交平台配置，渲染对应的图标按钮。
    使用弹窗（window.open）方式打开 OAuth 授权页，
    授权完成后由 social_callback.blade.php 关闭弹窗并跳转至用户中心。
    此文件被 login.blade.php 和 register.blade.php 通过 @include 引用。

  【访问权限】
    无需登录（公开访问，随登录/注册页使用）。

  【对应路由/控制器】
    OAuth 授权跳转路由：front_root_route('social.redirect', ['provider' => 'github'])
    授权回调路由：由后端处理（通常为 front_root_route('social.callback', ['provider' => ...])）
    控制器：Front\Auth\SocialController@redirect / @callback

  【可用变量】
    无控制器注入变量。
    通过 system_setting('social') 从系统设置中读取社交平台配置，格式为：
      [
        ['provider' => 'google', 'active' => true],
        ['provider' => 'github', 'active' => false],
        ...
      ]
    仅 active = true 的平台会显示。

  【Sections / Push】
    footer → 定义 openSocialLogin(url) 函数（@push），
             使用 window.open 以居中弹窗方式打开 OAuth 授权页（宽600 高600）

  【插件钩子】
    本文件无 @hookinsert 插入点。

  【图标映射】
    按钮图标使用 Bootstrap Icons（bi bi-{provider}），
    因此 provider 值需与 Bootstrap Icons 中的图标名匹配，例如：
      google → bi-google
      github → bi-github
      facebook → bi-facebook
      twitter → bi-twitter

  【自定义建议】
    - 若系统未启用任何社交平台，整个 div 区块不会渲染（条件判断）
    - 弹窗尺寸（width=600, height=600）可在 openSocialLogin JS 函数中调整
    - 可将图标替换为品牌 Logo 图片，修改 <i class="bi bi-..."> 为 <img src="...">
    - 若需在按钮旁显示平台名称，在 <a> 标签内追加文字即可
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    社交登录按钮局部模板（Social Login Buttons Partial）。
    在登录页（login.blade.php）和注册页（register.blade.php）的表单底部引入，
    当系统设置中有启用的社交登录提供商时，显示"or"分隔线和社交按钮组。
    点击按钮后通过 openSocialLogin() 打开 600×600 弹窗完成第三方授权。
    授权回调由 social_callback.blade.php 处理（弹窗关闭并刷新主窗口）。

  【引用方式】
    @include('account._social')
    通常在登录/注册表单的 </form> 标签后调用。

  【可用变量（来自全局系统设置）】
    system_setting('social') — array，社交登录配置列表，每条含：
                                 provider  提供商 code（如 google/github/facebook）
                                 active    是否启用（bool）

  【前端交互】
    - openSocialLogin(url) — 在居中弹窗中打开第三方授权页
    - 授权完成后回调页 social_callback.blade.php 自动刷新主窗口并关闭弹窗
    - 按钮图标使用 Bootstrap Icons：bi bi-{provider}（如 bi bi-google）

  【自定义建议】
    - 可将弹窗尺寸（600×600）在 @push('footer') JS 中调整
    - 社交按钮样式由 _social.scss 控制（.social-button）
    - 如需自定义按钮样式（如显示提供商名称），修改 .social-button 内部结构
  ============================================================
--}}
@if(collect(system_setting('social'))->where('active', true)->count())
  <div class="d-flex align-items-center mt-3">
    <div class="line"></div>
    <div class="word fs-3 mb-1 mx-3">or</div>
    <div class="line"></div>
  </div>

  <div class="d-flex flex-wrap justify-content-center">
    @foreach(system_setting('social') as $provider)
      @if($provider['active'])
        <div class="social-button mt-4 mx-4 d-flex align-items-center justify-content-center">
          <a href="javascript:void(0)"
             onclick="openSocialLogin('{{ front_root_route('social.redirect', ['provider' => $provider['provider']]) }}')"
             class="d-flex align-items-center justify-content-center w-100 text-decoration-none text-white fs-4">
            <i class="bi bi-{{ $provider['provider'] }} fs-3"></i>
          </a>
        </div>
      @endif
    @endforeach
  </div>
@endif

@push('footer')
  <script>
    function openSocialLogin(url) {
      const width = 600;
      const height = 600;
      const left = (window.innerWidth / 2) - (width / 2);
      const top = (window.innerHeight / 2) - (height / 2);
      window.open(url, 'socialLogin', `width=${width},height=${height},top=${top},left=${left}`);
    }
  </script>
@endpush