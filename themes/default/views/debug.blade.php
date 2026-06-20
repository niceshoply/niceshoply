{{--
  ============================================================
  【文件说明】
    前台调试视图模板（Debug View）。
    用于在开发/调试阶段快速将控制器传入的数据以可读格式输出到页面，
    方便开发者检查变量内容、数据结构等。
    该页面是独立的完整 HTML 文档，不继承 layouts.app，
    渲染极简，仅包含必要的 <head> 和 <body>。

    ⚠️ 注意：此页面仅在开发环境或由特定调试路由触发时使用，
    请勿在生产环境暴露此页面，避免数据泄露安全风险。

  【对应路由/控制器】
    由 nice_view() 函数在特定调试模式下调用，或直接通过调试路由访问。
    控制器传入 $data 变量（内容因调试场景而异）。

  【可用变量】
    $data — 任意类型，由调用方传入的待调试数据对象/数组。
            通过 Laravel 的 @dump() 指令以人类可读格式展开输出。

  【Sections/Blocks】
    @yield('title') — 页面标题，默认为 system_setting_locale('meta_title', 'NiceShoply DebugBar')

  【全局 PHP 辅助函数（在此文件中使用）】
    system_setting_locale('meta_title') — 读取当前语言的站点标题（用作页面标题）
    front_locale_direction()            — 当前语言文字方向（'ltr' 或 'rtl'）

  【包含的局部模板】
    无

  【插件钩子】
    无

  【调试输出说明】
    @dump($data) 等价于 Laravel 的 dump() 函数，使用 Symfony VarDumper 组件
    以树状结构展开并高亮显示变量内容，方便查看嵌套对象/数组。

  【自定义建议】
    - 一般情况下无需修改此文件。
    - 如需调试特定变量，可在调用 nice_view() 前修改传入的 $data 数组内容。
    - 如需格式化输出多个变量，可将此文件的 @dump($data) 替换为
      @dump($var1, $var2, ...) 来同时输出多个变量。
    - 生产上线前，确认此调试路由/视图已被禁用或添加访问鉴权。
  ============================================================
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ front_locale_direction() }}">

<head>
  <meta charset="utf-8">
  <title>@yield('title', system_setting_locale('meta_title', 'NiceShoply DebugBar'))</title>
</head>

<body>
  @dump($data)
</body>

</html>
