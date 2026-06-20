{{--
  ============================================================
  【文件说明】
    前台维护模式页面视图模板。
    当系统在后台设置为"维护模式"时，所有前台访问将被重定向到此页面。
    页面展示维护中插图、标题和说明文字，提示用户当前商城暂停服务。

  【对应路由/控制器】
    路由：由中间件（Maintenance Middleware）拦截后跳转，
          或通过专属路由 front.maintenance 直接访问。
    控制器：无专属控制器，由中间件直接渲染此视图。

  【可用变量】
    无控制器注入变量。所有内容通过翻译函数和静态资源获取。

  【Sections/Blocks】
    @section('body-class', 'page-maintenance') — 为 <body> 添加 'page-maintenance' 类
    @section('content')                        — 注入维护页面内容到 layouts.app 的主内容区

  【翻译键（i18n）】
    __('front/maintenance.title')       — 维护页面主标题（如："商城维护中"）
    __('front/maintenance.description') — 维护页面描述文字（如："我们正在进行系统升级，请稍后访问"）
    可在语言包文件 lang/{locale}/front/maintenance.php 中自定义翻译内容。

  【页面结构说明】
    采用左右两栏 Flexbox 布局（PC 端），移动端自动切换为上下堆叠：
    ┌────────────────────────────────────────┐
    │  [维护图片 45%]   [标题 + 说明文字 45%] │  PC 端：左右并排
    └────────────────────────────────────────┘
    ┌─────────────────┐
    │   维护图片       │  移动端（< 992px）：图片在上，文字在下，居中对齐
    │   标题           │
    │   说明文字        │
    └─────────────────┘
    整体最小高度 60vh，垂直居中，白色背景。

  【静态资源】
    images/maintenance.svg — 维护状态插图（SVG），路径相对于 public/ 目录。
                             可替换为品牌风格的维护插图，建议使用 SVG 以保证清晰度。
                             图片宽度 100%，PC 端最大宽 400px，移动端最大宽 300px。

  【内联样式说明】
    此模板包含 <style> 块定义维护页专属样式，未通过外部 CSS 文件加载。
    类名说明：
      .maintenance-page     — 页面全屏居中容器，min-height: 60vh
      .maintenance-content  — 内容区最大宽 900px，左右并排 Flex 布局
      .maintenance-image    — 图片区，占 45% 宽度，最大 400px
      .maintenance-text     — 文字区，占 45% 宽度
      .maintenance-text h1  — 主标题，2.5rem，color: #333，font-weight: 600
      .maintenance-text p   — 描述文字，1.25rem，color: #666，行高 1.6

  【包含的局部模板】
    无

  【插件钩子】
    无（维护模式下插件系统可能未完全加载，保持简洁更稳定）

  【自定义建议】
    - 在自定义主题中复制此文件到 themes/{code}/views/maintenance.blade.php 进行覆盖。
    - 可替换 images/maintenance.svg 为品牌专属插图。
    - 可在标题和描述文字下方添加倒计时组件或社交媒体联系方式。
    - 如需添加"管理员登录"入口，可在 .maintenance-text 中添加链接，
      指向后台登录地址 {{ route('admin.login') }}。
    - 内联 <style> 块中的品牌色可按需修改（当前为标准灰黑色调）。
    - 移动端断点当前为 991px（Bootstrap lg 断点），与整体布局保持一致。
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-maintenance')

@section('content')
<div class="maintenance-page">
    <div class="container">
        <div class="maintenance-content">
            <div class="maintenance-image">
                <img src="{{ asset('images/maintenance.svg') }}" alt="Store Closed">
            </div>
            <div class="maintenance-text">
                <h1>{{ __('front/maintenance.title') }}</h1>
                <p>{{ __('front/maintenance.description') }}</p>
            </div>
        </div>
    </div>
</div>

<style>
.maintenance-page {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
}

.maintenance-content {
    max-width: 900px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 3rem;
}

.maintenance-image {
    flex: 0 0 45%;
    max-width: 400px;
}

.maintenance-image img {
    width: 100%;
    height: auto;
}

.maintenance-text {
    flex: 0 0 45%;
}

.maintenance-text h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 1rem;
    font-weight: 600;
}

.maintenance-text p {
    font-size: 1.25rem;
    color: #666;
    line-height: 1.6;
}

@media (max-width: 991px) {
    .maintenance-content {
        flex-direction: column;
        text-align: center;
        gap: 2rem;
    }

    .maintenance-image {
        flex: 0 0 100%;
        max-width: 300px;
    }

    .maintenance-text {
        flex: 0 0 100%;
    }
}
</style>
@endsection 