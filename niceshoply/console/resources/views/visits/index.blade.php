@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/visit.detail_title'))
@section('page-title-right')
  <div class="d-flex gap-2">
    <a href="{{ console_route('visits.statistics') }}" class="btn btn-outline-primary">
      <i class="bi bi-bar-chart"></i> {{ __('console/visit.statistics_title') }}
    </a>
    <button type="button" class="btn btn-primary" id="btn-enrich" @disabled(!$geo_available)>
      <i class="bi bi-geo-alt"></i> {{ __('console/visit.enrich') }}
    </button>
  </div>
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">

      @unless($geo_available)
        <div class="alert alert-warning d-flex align-items-center" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          <div>{{ __('console/visit.geo_unavailable') }}</div>
        </div>
      @endunless

      <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('visits.index')"/>

      @if ($visits->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('console/common.id') }}</td>
              <td>{{ __('console/visit.customer') }}</td>
              <td>{{ __('console/visit.ip_address') }}</td>
              <td>{{ __('console/visit.location') }}</td>
              <td>{{ __('console/visit.device') }}</td>
              <td>{{ __('console/visit.browser_os') }}</td>
              <td>{{ __('console/visit.referrer') }}</td>
              <td>{{ __('console/visit.first_visited_at') }}</td>
              <td>{{ __('console/visit.last_visited_at') }}</td>
              @hookinsert('console.visits.index.thead.bottom')
            </tr>
            </thead>
            <tbody>
            @foreach($visits as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  @if($item->customer)
                    <a href="{{ console_route('customers.edit', [$item->customer->id]) }}" class="text-decoration-none">
                      {{ $item->customer->name ?: $item->customer->email }}
                    </a>
                  @else
                    <span class="badge bg-secondary">{{ __('console/visit.guest') }}</span>
                  @endif
                </td>
                <td>{{ $item->ip_address }}</td>
                <td>
                  @if($item->country_name || $item->city)
                    {{ trim(($item->country_name ?? '').' '.($item->city ?? '')) }}
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>{{ $item->device_type_display }}</td>
                <td>
                  {{ $item->browser ?: '-' }}
                  <small class="text-muted d-block">{{ $item->os ?: '-' }}</small>
                </td>
                <td class="text-truncate" style="max-width: 220px;" title="{{ $item->referrer }}">
                  {{ $item->referrer ?: '-' }}
                </td>
                <td>{{ $item->first_visited_at }}</td>
                <td>{{ $item->last_visited_at }}</td>
                @hookinsert('console.visits.index.tbody.bottom', $item)
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $visits->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection

@push('footer')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const enrichBtn = document.getElementById('btn-enrich');
      if (!enrichBtn) {
        return;
      }

      enrichBtn.addEventListener('click', function () {
        const originalHtml = enrichBtn.innerHTML;
        enrichBtn.disabled = true;
        enrichBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> {{ __('console/common.loading') }}';

        fetch('{{ console_route('visits.enrich') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        }).then(response => response.json().then(data => {
          if (response.ok && data.success) {
            inno.msg(data.message || '{{ __('console/visit.enrich_done') }}');
            setTimeout(() => window.location.reload(), 1000);
          } else {
            inno.msg(data.message || data.error || '{{ __('console/visit.enrich_failed') }}');
            enrichBtn.disabled = false;
            enrichBtn.innerHTML = originalHtml;
          }
        })).catch(() => {
          inno.msg('{{ __('console/visit.enrich_failed') }}');
          enrichBtn.disabled = false;
          enrichBtn.innerHTML = originalHtml;
        });
      });
    });
  </script>
@endpush
