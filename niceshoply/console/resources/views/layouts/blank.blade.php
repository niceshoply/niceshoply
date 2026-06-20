<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ console_locale_direction() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ console_route('home.index') }}">
  <title>@yield('title', '') - NiceShoply</title>
  <meta name="keywords" content="@yield('keywords', 'NiceShoply, 创新, 开源, CMS, Laravel 11, 多语言, 多货币, Hook, 插件架构, 灵活, 强大')">
  <meta name="generator" content="NiceShoply {{ niceshoply_version() }}">
  <meta name="asset" content="{{ asset('/') }}">
  <meta name="description" content="@yield('description', 'NiceShoply')">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="api-token" content="{{ session('console_api_token') }}">
  <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">

  <!-- 基础样式和脚本 -->
  <link rel="stylesheet" href="{{ build_asset('console/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ build_asset('console/css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <script src="{{ build_asset('console/js/app.js') }}"></script>
  <script>
    let urls = {
      base_url: '{{ console_route('home.index') }}',
      upload_images: '{{ console_route('upload.images') }}',
      ai_generate: '{{ console_route('content_ai.generate') }}',
      ai_status: '{{ console_route('content_ai.status') }}',
    }

    const lang = {
      hint: '{{ __('console/common.hint') }}',
      delete_confirm: '{{ __('console/common.delete_confirm') }}',
      confirm: '{{ __('console/common.confirm') }}',
      cancel: '{{ __('console/common.cancel') }}',
    }
  </script>
  @stack('header')
</head>

<body class="@yield('body-class')">
  <div class="container-fluid">
    @yield('content')
      <div class="page-bottom-btns my-4">
          @yield('page-bottom-btns')
      </div>
  </div>
  @stack('footer')
</body>

</html>
