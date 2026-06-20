{{--
  ============================================================
  【文件说明】
    503 服务不可用错误页面（维护模式页面）。
    当网站处于维护模式（php artisan down）或服务器不可用时显示此页面。
    页面居中显示大号「503」数字 + 说明文字 + 两个操作按钮：
      - 刷新页面（location.reload()）
      - 返回首页（front_route('home.index')）

  【触发场景】
    - 执行 php artisan down 开启维护模式后，所有访客看到此页面
    - 服务器返回 503 状态码时（负载过高、依赖服务宕机等）
    - 应用抛出 ServiceUnavailableHttpException

  【对应路由 / 触发方式】
    由 Laravel 异常处理器自动渲染，无需手动定义路由。
    文件路径约定：resources/views/errors/503.blade.php
    注意：维护模式下 Laravel 会优先使用 resources/views/errors/503.blade.php
         （若存在 storage/framework/maintenance.php 则走维护模式逻辑）

  【可用变量】
    $exception — Exception  可选，Laravel 传入的异常对象（通常不直接使用）

  【SEO 设置】
    @section('title') — 拼接语言文案 front/common.service_unavailable 和 config('app.name')

  【Sections】
    body-class → page-error device-pc
    title      → 页面标题（服务不可用 - 站点名称）
    content    → 错误内容区

  【样式说明】
    .error-code        — 大号错误码数字，颜色使用 Bootstrap 警告色 --bs-warning（黄色）
    .error-code::after — 伪元素渲染透明背景大字，增强视觉层次

  【辅助函数】
    front_route('home.index') — 返回首页按钮链接

  【与 maintenance.blade.php 的区别】
    - maintenance.blade.php 是 NiceShoply 系统自身「商店维护中」功能页面（后台可设置）
    - 503.blade.php 是 Laravel 框架级别的错误处理页面（php artisan down 或系统故障）

  【自定义建议】
    - 可增加预计恢复时间的提示（从配置文件或环境变量读取）
    - 可增加客服联系方式，方便有紧急需求的用户联系
    - 维护模式下可在 php artisan down --secret=xxx 的情况下绕过维护页进行测试
    - 警告色数字（黄色）与 404（蓝色）形成视觉区分，符合语义
    - 语言文案在 resources/lang/{locale}/front/common.php 中定义
  ============================================================
--}}
@extends('layouts.app')

@section('title', __('front/common.service_unavailable') . ' - ' . config('app.name'))

@section('body-class', 'page-error device-pc')

@section('content')
  <div class="container py-5">
    <div class="row align-items-center justify-content-center" style="min-height: calc(100vh - 400px);">
      <div class="col-md-6 text-center">
        <div class="mb-4">
          <div class="error-code" style="font-size: 120px; line-height: 1; color: var(--bs-warning, #ffc107); font-weight: 300;">
            503
          </div>
          <h2 class="h4 mb-3 mt-4">{{ __('front/common.service_unavailable') }}</h2>
          <p class="text-secondary mb-4">{{ __('front/common.service_unavailable_description') }}</p>
        </div>
        <div class="d-flex gap-3 justify-content-center">
          <a href="javascript:;" onclick="location.reload();" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i> {{ __('front/common.refresh') }}
          </a>
          <a href="{{ front_route('home.index') }}" class="btn btn-primary">
            <i class="bi bi-house me-1"></i> {{ __('front/common.home') }}
          </a>
        </div>
      </div>
    </div>
  </div>

  <style>
    .error-code {
      text-shadow: 4px 4px 10px rgba(255, 193, 7, 0.1);
      position: relative;
    }

    .error-code::after {
      content: "503";
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      font-size: 140px;
      opacity: 0.03;
      letter-spacing: 0.1em;
    }
  </style>
@endsection

