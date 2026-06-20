<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ console_locale_direction() }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <base href="{{ console_route('home.index') }}">
  <title>@yield('title', __('console/login.title'))</title>
  <meta name="keywords" content="@yield('keywords', __('console/login.keywords'))">
  <meta name="description" content="@yield('description', __('console/login.description'))">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}">
  <link rel="stylesheet" href="{{ build_asset('console/css/bootstrap.css') }}">
  <link rel="stylesheet" href="{{ build_asset('console/css/app.css') }}">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ build_asset('console/js/app.js') }}"></script>
  <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('vendor/layer/3.5.1/layer.js') }}"></script>
  @stack('header')
</head>
<body class="page-login">
  <div class="login-page">
    <section class="login-left">
      <a class="login-brand" href="{{ front_route('home.index') }}">
        <x-console::brand-logo class="logo" />
        <div>
          <h1>{{ system_setting('name', config('app.name', 'NiceShoply')) }}</h1>
          <p>{{ __('console/login.title') }}</p>
        </div>
      </a>
      <div class="login-showcase">
        <span class="eyebrow">{{ __('console/login.eyebrow') }}</span>
        <h1>{{ __('console/login.showcase_title') }}</h1>
        <p>{{ __('console/login.showcase_desc') }}</p>
      </div>
      <div class="mini text-secondary">
        {!! niceshoply_brand_link() !!} {{ niceshoply_version() }} &copy; {{ date('Y') }}
      </div>
    </section>

    <section class="login-right">
      <div class="login-card">
        <div class="login-locale locale">
          <span class="locale-toggle d-inline-flex align-items-center" data-bs-toggle="dropdown">
            <span class="wh-20 me-2"><img src="{{ image_origin('images/flag/'. console_locale_code().'.png') }}" class="img-fluid"></span>
            <span>{{ current_console_locale()['name'] }}</span>
            <i class="bi bi-chevron-down ms-1"></i>
          </span>
          <ul class="dropdown-menu dropdown-menu-end locale-dropdown-menu">
            @foreach (console_locales() as $locale)
            <li>
              <a class="dropdown-item d-flex align-items-center" href="{{ console_route('login.index', ['locale'=> $locale['code']]) }}">
                <span class="wh-20 me-2"><img src="{{ image_origin($locale['image']) }}" class="img-fluid border"></span>
                {{ $locale['name'] }}
              </a>
            </li>
            @endforeach
          </ul>
        </div>

        <h2>{{ __('console/login.welcome_back') }}</h2>
        <p class="login-card-sub">{{ __('console/login.welcome_subtitle') }}</p>

        <form action="{{ console_route('login.store') }}" method="post">
          @csrf

          <div class="form-floating mb-3">
            <input type="text" name="email" class="form-control" id="email-input" value="{{ old('email', $admin_email ?? '') }}" placeholder="{{ __('common.email') }}">
            <label for="email-input">{{ __('console/login.email') }}</label>
            @error('email')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <div class="form-floating mb-4">
            <input type="password" name="password" class="form-control" id="password-input" value="{{ old('password', $admin_password ?? '') }}" placeholder="{{ __('shop/login.password') }}">
            <label for="password-input">{{ __('console/login.password') }}</label>
            @error('password')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          @if (session('error'))
            <div class="alert alert-danger">
              {{ session('error') }}
            </div>
          @endif

          <div class="d-grid"><button type="submit" class="btn btn-lg btn-primary">{{ __('console/common.btn_submit') }}</button></div>
        </form>
      </div>
    </section>
  </div>
</body>
</html>
