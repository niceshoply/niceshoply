{{--
  ============================================================
  【文件说明】
    邮件内容布局模板。
    系统发送的所有 HTML 邮件（订单确认、密码重置、发货通知等）
    均通过 @extends('layouts.mail') 继承此布局。
    该模板不是完整 HTML 文档，而是一段内联样式的 HTML 片段，
    适用于直接嵌入邮件客户端渲染（无需 <html>/<head> 标签）。

  【对应路由/控制器】
    不直接对应路由。
    由邮件 Mailable 类（位于 app/Mail/ 目录）通过 ->view('layouts.mail')
    或子视图 @extends('layouts.mail') 调用。

  【可用变量（由子视图 @section 填充）】
    @yield('content') — 邮件主体内容区域（必填），渲染为 <table> 内的行列

  【全局 PHP 辅助函数（在此文件中使用）】
    front_route('home.index')       — 生成前台首页完整 URL（邮件中 Logo 超链接目标）
    image_origin($path)             — 将相对路径转为图片完整 URL（用于 Logo 图片）
    system_setting('front_logo')    — 读取前台 Logo 图片路径（系统设置中配置）
    config('app.name')              — 读取应用名称（用于底部版权信息）
    date('Y')                       — 当前年份（用于版权年份展示）

  【Sections/Blocks（子视图必须实现）】
    @yield('content') — 邮件正文部分，内容将渲染在白色卡片区域内的 <table> 中。
                        子视图应输出 <tr><td>...</td></tr> 格式的行内容。

  【模板结构说明】
    整体布局采用纯 Table 布局（兼容各邮件客户端）：
    ┌─────────────────────────────────────┐
    │  Logo（链接到前台首页）              │
    │  紫色分割线（品牌色 #944FE8）        │
    │  白色内容区（@yield('content') ）   │
    │  底部版权信息（App名 © 年份）        │
    └─────────────────────────────────────┘
    最大宽度 660px，最小宽度 320px，自动水平居中，
    背景色 #f7f8fa，内容区背景色 #ffffff。

  【包含的局部模板】
    无，该文件为独立邮件布局，不 include 其他 Blade 片段。

  【插件钩子】
    无钩子插入点。

  【自定义建议】
    - 在自定义主题中复制此文件到 themes/{code}/views/layouts/mail.blade.php 进行覆盖。
    - 可修改顶部分割线颜色（当前为 #944FE8）以匹配品牌主色调。
    - 如需添加社交媒体图标或链接，可在底部版权区 </td> 前插入。
    - Logo 高度固定为 35px，如需更大 Logo，修改 .style="height: 35px" 的内联样式。
    - 注意：邮件模板必须使用内联样式，不可引用外部 CSS 文件（邮件客户端不支持）。
    - 品牌色可通过修改 background-color:#944FE8 的分割线样式全局调整。
  ============================================================
--}}
<div align="center">
  <div class="" style="margin-left: 8px; margin-top: 8px; margin-bottom: 8px; margin-right: 8px;">
    <div style="word-break: break-all;box-sizing:border-box;text-align:center;min-width:320px; max-width:660px; border:1px solid #f6f6f6; background-color:#f7f8fa; margin:auto; padding:20px 0 30px; font-family:'helvetica neue',PingFangSC-Light,arial,'hiragino sans gb','microsoft yahei ui','microsoft yahei',simsun,sans-serif">
      <table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse">
        <tbody>
          <tr style="font-weight:300">
            <td style="width:3%;max-width:30px;"></td>
            <td style="max-width:600px;">
              <div style="height: 35px;display: block;max-width: 200px;">
                <a href="{{ front_route('home.index') }}"
                   style="display: block;height: 35px;display: block;max-width: 200px;">
                  <img border="0" src="{{ image_origin(system_setting('front_logo')) }}"
                       style="max-width:100%; max-height: 100%;">
                </a>
              </div>

              <p style="height:2px;background-color: #944FE8;border: 0;font-size:0;padding:0;width:100%;margin-top:10px;"></p>
              <div
                  style="background-color:#fff; padding:23px 0 20px;box-shadow: 0px 1px 1px 0px rgba(122, 55, 55, 0.2);text-align:left;">
                <table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse;text-align:left;">
                  @yield('content')
                </table>
              </div>

              <div style="margin-top: 10px; text-align:center; font-size:12px; line-height:18px; color:#999">
                <table style="width:100%;font-weight:300;margin-bottom:10px;border-collapse:collapse">
                  <tbody>
                  <tr style="font-weight:300">
                    <td style="width:3.2%;max-width:30px;"></td>
                    <td style="max-width:540px;">
                      <p style="max-width: 100%; margin:auto;font-size:12px;color:#999;text-align:center;line-height:22px;">
                        {{ config('app.name') }} &copy; {{ date('Y') }} All Rights Reserved
                        </p>
                      </td>
                      <td style="width:3.2%;max-width:30px;"></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </td>
            <td style="width:3%;max-width:30px;"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
