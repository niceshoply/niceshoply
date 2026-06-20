{{--
  ============================================================
  【文件说明】
    第三方社交登录回调页（OAuth Callback 跳转处理页）。
    此页面不包含任何可见 HTML，仅包含一段 JavaScript。
    后端 OAuth 处理完成后渲染此视图，由 JS 判断当前窗口情况：
    - 若在弹窗中打开（window.opener 存在）：
        将父页面（opener）跳转至用户中心首页，然后关闭弹窗。
    - 若直接访问（window.opener 为 null）：
        当前窗口直接跳转至用户中心首页。

  【访问权限】
    无需登录（由后端 OAuth 控制器在认证后调用此视图渲染）。

  【对应路由/控制器】
    路由通常为：/{locale}/social/{provider}/callback（GET）
    控制器：Front\Auth\SocialController@callback
    此视图由控制器在 OAuth 流程完成后直接 return view('account.social_callback') 渲染。

  【可用变量】
    无控制器注入变量。
    front_route('account.index') — 用户中心首页 URL（硬编码到 JS 变量 url 中）

  【Sections】
    本页面不继承任何布局，也不定义任何 @section，
    直接输出纯 <script> 标签。

  【插件钩子】
    本文件无 @hookinsert 插入点。

  【自定义建议】
    - 若 OAuth 登录后需跳转至来源页（referrer）而非固定用户中心，
      可在控制器中将 redirect_uri 传入视图，替换 front_route('account.index')
    - 若要在关闭弹窗前显示"登录成功"提示，可在 window.opener 分支中添加：
        window.opener.layer.msg('登录成功', {icon: 1, time: 1000});
      （需父页面已加载 layer.js）
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    社交登录回调页（Social Login Callback）。
    这是一个纯脚本页面，不继承任何布局，仅包含一段 JS。
    当社交登录弹窗（由 _social.blade.php 中的 openSocialLogin() 打开）完成
    第三方授权并回调成功后，本页面被加载执行：

    逻辑说明：
      - 若以弹窗方式打开（window.opener 不为 null）：
        将主窗口（opener）重定向至用户中心首页，并关闭弹窗
      - 若以普通方式打开（window.opener 为 null，如被拦截或直接访问）：
        直接将当前窗口重定向至用户中心首页

  【对应路由/控制器】
    路由名称（GET）：front.social.callback（或由插件注册的社交登录回调路由）
    URL 示例：/{locale}/social/{provider}/callback
    控制器：Front\Account\SocialController@callback

  【可用变量】
    无需控制器注入变量，仅使用 front_route() 辅助函数生成跳转 URL。

  【自定义建议】
    - 如需在登录成功后跳转至来源页（而非用户中心），
      可在控制器 session 中保存 intended URL，
      并在此修改 front_route('account.index') 为 session('url.intended') 的值
    - 若需显示"登录成功"提示，可在跳转前添加 localStorage 写入，
      再由主窗口读取并展示 Toast 消息
  ============================================================
--}}
<script>
  const url = "{{ front_route('account.index') }}";
  if (window.opener === null) {
    window.location.href = url;
  } else {
    window.opener.location = url;
    window.close();
  }
</script>
