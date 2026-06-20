{{--
  ============================================================
  【文件说明】
    404 页面未找到错误页面。
    当用户访问不存在的页面时，Laravel 自动渲染此模板。
    页面居中显示大号「404」数字 + 错误说明 + 两个操作按钮：
      - 返回上一页（JS history.back()）
      - 返回首页（front_route('home.index')）

  【触发场景】
    - 访问了不存在的 URL 路径
    - 路由匹配失败，抛出 ModelNotFoundException 或 NotFoundHttpException
    - 控制器手动调用 abort(404)

  【对应路由 / 触发方式】
    由 Laravel 异常处理器自动渲染，无需手动定义路由。
    文件路径约定：resources/views/errors/404.blade.php

  【可用变量】
    $exception — Exception  可选，Laravel 传入的异常对象（通常不直接使用）

  【SEO 设置】
    @section('title') — 拼接语言文案 front/common.page_not_found 和 config('app.name')

  【Sections】
    body-class → page-error device-pc
    title      → 页面标题（页面未找到 - 站点名称）
    content    → 错误内容区

  【样式说明】
    .error-code        — 大号错误码数字，颜色使用 Bootstrap 主色 --bs-primary
    .error-code::after — 伪元素渲染透明背景大字，增强视觉层次

  【辅助函数】
    front_route('home.index') — 返回首页按钮链接

  【自定义建议】
    - 可将背景数字替换为品牌插图/吉祥物图片，提升页面趣味性
    - 可增加站内搜索框，帮助用户快速找到所需内容
    - 可增加热门商品或推荐分类，挽留流失用户
    - 可加入错误上报代码（如 Sentry/GA 事件）追踪 404 来源
    - 语言文案在 resources/lang/{locale}/front/common.php 中定义
  ============================================================
--}}
@extends('layouts.app')

@section('title', __('front/common.page_not_found') . ' - ' . config('app.name'))

@section('body-class', 'page-error device-pc')

@section('content')
  <div class="container py-5">
    <div class="row align-items-center justify-content-center" style="min-height: calc(100vh - 400px);">
      <div class="col-md-6 text-center">
        <div class="mb-4">
          <div class="error-code" style="font-size: 120px; line-height: 1; color: var(--bs-primary, #0d6efd); font-weight: 300;">
            404
          </div>
          <h2 class="h4 mb-3 mt-4">{{ __('front/common.page_not_found') }}</h2>
          <p class="text-secondary mb-4">{{ __('front/common.page_not_found_description') }}</p>
        </div>
        <div class="d-flex gap-3 justify-content-center">
          <a href="javascript:;" onclick="history.back();" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> {{ __('front/common.back_page') }}
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
      text-shadow: 4px 4px 10px rgba(13, 110, 253, 0.1);
      position: relative;
    }

    .error-code::after {
      content: "404";
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

