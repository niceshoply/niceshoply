@extends('layouts.app')

@section('body-class', 'page-news-details device-' . ($device ?? 'pc'))

@section('title', \NiceShoply\Common\Libraries\MetaInfo::getInstance($page)->getTitle())
@section('description', \NiceShoply\Common\Libraries\MetaInfo::getInstance($page)->getDescription())
@section('keywords', \NiceShoply\Common\Libraries\MetaInfo::getInstance($page)->getKeywords())

@php
  $isDesignMode = request()->get('design');
  $hasModules = !empty($modules);
@endphp

@push('header')
  @if($hasModules || $isDesignMode)
    <script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/front/rich-text-responsive.css') }}">
    <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/front/multi-row-images-responsive.css') }}">
  @endif
  @if($isDesignMode)
    <link rel="stylesheet" href="{{ plugin_asset('PageBuilder', 'css/front/design-mode.css') }}">
  @endif
@endpush

@section('content')

  @if(!$isDesignMode)
    {{-- Normal mode: show page structure + builder modules fused --}}

    @if($page->show_breadcrumb)
      <x-front-breadcrumb type="page" :value="$page" />
    @endif

    @hookinsert('page.show.top')

    @if($hasPageContent ?? false)
      <div class="container mt-3 mt-md-5">
        <div class="row justify-content-center">
          <div class="col-12">
            <div class="newest-box">
              <div class="newes-title">{{ $page->translation->title }}</div>
              <div class="newes-content">
                {!! $page->translation->content !!}
              </div>
            </div>
          </div>
        </div>
      </div>
    @endif

    @if($hasModules)
      <div class="m-0 p-0">
        <section class="module-content">
          <div id="page-modules-box" class="modules-box">
            @foreach ($modules as $module)
              <section id="module-{{ $module['module_id'] ?? $loop->index }}" class="module-item" data-module-id="{{ $module['module_id'] ?? $loop->index }}">
                <div class="module-content">
                  @if (View::exists('PageBuilder::front.modules.' . $module['code']))
                    @include('PageBuilder::front.modules.' . $module['code'], [
                      'module' => $module,
                      'content' => $module['content'],
                      'module_id' => $module['module_id'] ?? $loop->index,
                    ])
                  @endif
                </div>
              </section>
            @endforeach
          </div>
        </section>
      </div>
    @endif

    @hookinsert('page.show.bottom')

  @else
    {{-- Design mode: builder structure only (for iframe editing) --}}

    <div class="m-0 p-0" id="appContent">
      <section class="module-content">
        <div id="page-modules-box" class="modules-box">
          @if($hasModules)
            @foreach ($modules as $module)
              <section id="module-{{ $module['module_id'] ?? $loop->index }}" class="module-item module-item-design" data-module-id="{{ $module['module_id'] ?? $loop->index }}">
                @include('PageBuilder::front.partials.module-edit-buttons', ['module' => $module])
                <div class="module-content">
                  @if (View::exists('PageBuilder::front.modules.' . $module['code']))
                    @include('PageBuilder::front.modules.' . $module['code'], [
                      'module' => $module,
                      'content' => $module['content'],
                      'module_id' => $module['module_id'] ?? $loop->index,
                    ])
                  @endif
                </div>
              </section>
            @endforeach
          @else
            <div class="text-center py-5">
              <p>{{ __('PageBuilder::modules.no_modules_data') }}</p>
            </div>
          @endif
        </div>
      </section>
    </div>

  @endif

@endsection
