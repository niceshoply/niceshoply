<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ console_locale_direction() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ console_route('home.index') }}">
  <title>@yield('title'){{ View::hasSection('title') ? ' - ' : '' }}NiceShoply</title>
  <meta name="keywords" content="@yield('keywords', 'NiceShoply, 创新, 开源, CMS, Laravel 11, 多语言, 多货币, Hook, 插件架构, 灵活, 强大')">
  <meta name="generator" content="NiceShoply {{ niceshoply_version() }}">
  <meta name="asset" content="{{ asset('/') }}">
  <meta name="description" content="@yield('description', 'NiceShoply')">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="api-token" content="{{ session('console_api_token') }}">
  <link rel="shortcut icon" href="{{ image_origin(system_setting('favicon', 'images/favicon.png')) }}">
  <link rel="stylesheet" href="{{ asset('vendor/element-plus/index.css') }}">
  <link rel="stylesheet" href="{{ build_asset('console/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ build_asset('console/css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('vendor/vue/3.5/vue.global' . (config('app.debug') ? '' : '.prod') . '.js') }}"></script>
  <script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
  <script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  <script src="{{ build_asset('console/js/app.js') }}"></script>
  <script>
    const urls = {
      console_api: '{{ route('api.console.base.index') }}',
      console_base: '{{ console_route('home.index') }}',
      console_upload: '{{ console_route('upload.images') }}',
      console_ai: '{{ console_route('content_ai.generate') }}',
      console_ai_status: '{{ console_route('content_ai.status') }}',
    };

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
  <div class="main-content">
    <aside class="sidebar-box navbar-expand-xs border-radius-xl">
      <div class="sidebar-body">
        <a class="console-brand" href="{{ console_route('home.index') }}">
          <x-console::brand-logo />
          <span class="brand-text">
            <h1>{{ system_setting('name', config('app.name', 'NiceShoply')) }}</h1>
            <p>{{ __('console/login.title') }}</p>
          </span>
        </a>
        <x-console-layout-sidebar></x-console-layout-sidebar>
      </div>
      <div class="mb-menu-close"><i class="bi bi-chevron-left"></i></div>
    </aside>

    <div id="content">
      @include('console::layouts.header')

      <div class="page-title-box py-1 d-flex align-items-start justify-content-between">
        <div class="page-title-main">
          @hasSection('page-eyebrow')
            <span class="page-eyebrow">@yield('page-eyebrow')</span>
          @endif
          <div class="d-flex align-items-center">
            <h4 class="page-title mb-0">@yield('title')</h4>
            <div class="ms-4 text-danger">@yield('page-title-after')</div>
          </div>
          @hasSection('page-subtitle')
            <p class="page-subtitle mb-0">@yield('page-subtitle')</p>
          @endif
        </div>
        <div class="text-nowrap">
          @yield('page-title-right')
          @hookinsert('console.layout.right.button.after')
        </div>
      </div>

      <div class="container-fluid p-0 mt-2">
        <div class="content-info">
          @if (session()->has('errors'))
            <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4"/>
          @endif
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-4"/>
          @endif
          @yield('content')
        </div>

        <div class="page-bottom-btns">
          @yield('page-bottom-btns')
        </div>

        <p class="text-center text-secondary mt-5">
          {!! niceshoply_brand_link() !!}
          {{ niceshoply_version() }} &copy; {{ date('Y') }} All Rights Reserved
        </p>
      </div>
    </div>
  </div>

  @include('console::layouts.footer')

  @stack('footer')
</body>

</html>
