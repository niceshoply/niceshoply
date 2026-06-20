@extends('console::layouts.app')
@section('body-class', 'theme')

@section('title', __('console/menu.themes'))

@section('page-title-right')
  <a href="{{ console_route('theme_market.index') }}" class="btn btn-primary">{{ __('console/common.get_more') }}</a>
@endsection

@section('content')
<div class="card h-min-600">
  <div class="card-body p-4">
    @if($themes)
      <div class="themes-wrap">
        <div class="row g-4">
          @foreach($themes as $theme)
          <div class="col-6 col-lg-4 col-xxl-3">
            <div class="card themes-item overflow-hidden h-100 border @if($theme['selected']) theme-current @endif" data-theme-card="{{ $theme['code'] }}">
              <div class="theme-image-wrapper position-relative">
                <img src="{{ theme_image($theme['preview'], $theme['code'], 800, 600) }}" 
                     class="theme-preview-image" 
                     alt="{{ $theme['name'] }}"
                     data-preview-src="{{ theme_image($theme['preview'], $theme['code'], 1200, 900) }}">
                <div class="theme-overlay">
                  <button type="button" 
                          class="btn btn-light btn-sm theme-preview-btn"
                          data-bs-toggle="modal" 
                          data-bs-target="#themePreviewModal{{ $theme['code'] }}">
                    <i class="bi bi-zoom-in me-1"></i>
                    {{ __('console/common.preview') }}
                  </button>
                </div>
                {{-- 徽章常驻渲染，由 AJAX 切换 d-none，实现无整页刷新切换 --}}
                <div class="theme-current-badge @if(! $theme['selected']) d-none @endif">
                  <i class="bi bi-check-circle-fill me-1"></i>
                  {{ __('console/themes.current_theme') }}
                </div>
              </div>
              <div class="card-body d-flex flex-column">
                <div class="theme-header mb-3">
                  <h6 class="theme-name mb-2 fw-semibold @if($theme['selected']) text-primary @endif" data-theme-name="{{ $theme['code'] }}">
                    {{ $theme['name'] }}
                  </h6>
                  <div class="theme-meta d-flex align-items-center gap-3 text-muted small">
                    @if(isset($theme['version']) && $theme['version'])
                      <span class="theme-version d-flex align-items-center">
                        <i class="bi bi-tag-fill me-1" style="font-size: 0.7rem;"></i>
                        {{ $theme['version'] }}
                      </span>
                    @endif
                    @if(isset($theme['author']['name']) && $theme['author']['name'])
                      <span class="theme-author d-flex align-items-center">
                        <i class="bi bi-person-fill me-1" style="font-size: 0.7rem;"></i>
                        {{ $theme['author']['name'] }}
                      </span>
                    @endif
                  </div>
                </div>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                  <button type="button" 
                          class="btn btn-sm btn-outline-secondary"
                          data-bs-toggle="modal" 
                          data-bs-target="#themeDetail{{ $theme['code'] }}">
                    <i class="bi bi-eye me-1"></i>
                    {{ __('console/common.view') }}
                  </button>
                  {{-- 主题切换：AJAX 局部更新，无整页刷新 --}}
                  <div class="form-check form-switch theme-switch"
                       data-url="{{ console_route('themes.active', $theme['code']) }}"
                       data-code="{{ $theme['code'] }}">
                    <input class="form-check-input" type="checkbox" role="switch" @if($theme['selected']) checked @endif>
                  </div>
                </div>
              </div>
            </div>
          </div>

          @include('console::themes.modals.detail', ['theme' => $theme])
          @include('console::themes.modals.preview', ['theme' => $theme])
          
          @endforeach
        </div>
      </div>
    @else
    <x-common-no-data :text="__('console/themes.no_custom_theme')" />
    @endif
  </div>
</div>
@endsection

@push('footer')
<script>
  // 主题切换：调用后端 AJAX 接口，成功后局部更新卡片状态（高亮 / 徽章 / 开关），
  // 不再整页刷新（IMP：主题切换 AJAX 化）。主题为单选，激活一个即关闭其它。
  $(function () {
    $('.theme-switch > input[role="switch"]').on('change', function () {
      const $input  = $(this);
      const $wrap   = $input.parent();
      const url     = $wrap.data('url');
      const code    = String($wrap.data('code'));
      const status  = $input.prop('checked') ? 1 : 0;

      layer.load(2, { shade: [0.3, '#fff'] });
      axios.put(url, { status }).then((res) => {
        inno.msg(res.message);

        if (status) {
          // 激活当前主题：其它主题全部置为未激活
          $('.theme-switch > input[role="switch"]').each(function () {
            const $other = $(this);
            const otherCode = String($other.parent().data('code'));
            const on = otherCode === code;
            $other.prop('checked', on);
            updateThemeCard(otherCode, on);
          });
        } else {
          // 取消当前主题（恢复默认主题）：仅更新当前卡片
          updateThemeCard(code, false);
        }
      }).catch((err) => {
        // 失败回滚开关状态
        $input.prop('checked', !status);
        inno.msg(err.response?.data?.message || '{{ __('console/common.operation_failed') }}');
      }).finally(() => {
        layer.closeAll('loading');
      });
    });

    // 同步单个主题卡片的高亮 / 徽章 / 名称样式
    function updateThemeCard(code, active) {
      const $card  = $('[data-theme-card="' + code + '"]');
      const $badge = $card.find('.theme-current-badge');
      const $name  = $('[data-theme-name="' + code + '"]');

      $card.toggleClass('theme-current', active);
      $badge.toggleClass('d-none', !active);
      $name.toggleClass('text-primary', active);
    }
  });
</script>
@endpush
