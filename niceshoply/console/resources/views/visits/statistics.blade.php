@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/visit.statistics_title'))
@section('page-title-right')
  <div class="d-flex gap-2">
    <a href="{{ console_route('visits.index') }}" class="btn btn-outline-primary">
      <i class="bi bi-list-ul"></i> {{ __('console/visit.detail_title') }}
    </a>
    <button type="button" class="btn btn-primary" id="btn-aggregate">
      <i class="bi bi-arrow-repeat"></i> {{ __('console/visit.aggregate') }}
    </button>
  </div>
@endsection

@section('content')

  {{-- 周期与日期筛选 --}}
  <div class="card mb-3">
    <div class="card-body">
      <form action="{{ console_route('visits.statistics') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-auto">
          <label for="period" class="form-label form-label-sm">{{ __('console/visit.period') }}</label>
          <select name="period" id="period" class="form-select form-select-sm">
            <option value="day" {{ $period === 'day' ? 'selected' : '' }}>{{ __('console/visit.period_day') }}</option>
            <option value="week" {{ $period === 'week' ? 'selected' : '' }}>{{ __('console/visit.period_week') }}</option>
            <option value="month" {{ $period === 'month' ? 'selected' : '' }}>{{ __('console/visit.period_month') }}</option>
            <option value="year" {{ $period === 'year' ? 'selected' : '' }}>{{ __('console/visit.period_year') }}</option>
          </select>
        </div>
        <div class="col-auto">
          <label for="date" class="form-label form-label-sm">{{ __('console/visit.date') }}</label>
          <input type="date" name="date" id="date" class="form-control form-control-sm" value="{{ $date }}">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-search"></i> {{ __('console/common.search') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- 访问指标卡片 --}}
  <h6 class="text-muted mb-2">{{ __('console/visit.visit_metrics') }}</h6>
  <div class="row g-3 mb-4">
    @php
      $visit = $stats['visit'] ?? [];
      $visitCards = [
        ['label' => __('console/visit.pv'), 'value' => $visit['pv'] ?? 0, 'icon' => 'bi-eye', 'color' => 'primary'],
        ['label' => __('console/visit.uv'), 'value' => $visit['uv'] ?? 0, 'icon' => 'bi-people', 'color' => 'success'],
        ['label' => __('console/visit.ip'), 'value' => $visit['ip'] ?? 0, 'icon' => 'bi-hdd-network', 'color' => 'info'],
        ['label' => __('console/visit.new_visitors'), 'value' => $visit['new_visitors'] ?? 0, 'icon' => 'bi-person-plus', 'color' => 'warning'],
        ['label' => __('console/visit.bounces'), 'value' => $visit['bounces'] ?? 0, 'icon' => 'bi-box-arrow-right', 'color' => 'danger'],
        ['label' => __('console/visit.avg_duration'), 'value' => ($visit['avg_duration'] ?? 0) . ' s', 'icon' => 'bi-clock', 'color' => 'secondary'],
      ];
    @endphp
    @foreach($visitCards as $card)
      <div class="col-6 col-md-4 col-xl-2">
        <div class="card h-100">
          <div class="card-body text-center">
            <i class="bi {{ $card['icon'] }} fs-3 text-{{ $card['color'] }}"></i>
            <div class="fs-4 fw-bold mt-2">{{ $card['value'] }}</div>
            <div class="text-muted small">{{ $card['label'] }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- 设备分布 --}}
  <h6 class="text-muted mb-2">{{ __('console/visit.device_distribution') }}</h6>
  <div class="row g-3 mb-4">
    @php
      $deviceCards = [
        ['label' => __('console/visit.device_desktop'), 'value' => $visit['desktop_pv'] ?? 0, 'icon' => 'bi-display'],
        ['label' => __('console/visit.device_mobile'), 'value' => $visit['mobile_pv'] ?? 0, 'icon' => 'bi-phone'],
        ['label' => __('console/visit.device_tablet'), 'value' => $visit['tablet_pv'] ?? 0, 'icon' => 'bi-tablet'],
      ];
    @endphp
    @foreach($deviceCards as $card)
      <div class="col-12 col-md-4">
        <div class="card h-100">
          <div class="card-body d-flex align-items-center">
            <i class="bi {{ $card['icon'] }} fs-2 text-primary me-3"></i>
            <div>
              <div class="fs-4 fw-bold">{{ $card['value'] }}</div>
              <div class="text-muted small">{{ $card['label'] }}</div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- 转化漏斗 --}}
  <h6 class="text-muted mb-2">{{ __('console/visit.conversion_funnel') }}</h6>
  <div class="card mb-4">
    <div class="card-body">
      @php
        $conversion = $stats['conversion'] ?? [];
        $funnel = [
          ['label' => __('console/visit.product_views'), 'value' => $conversion['product_views'] ?? 0],
          ['label' => __('console/visit.add_to_carts'), 'value' => $conversion['add_to_carts'] ?? 0],
          ['label' => __('console/visit.checkout_starts'), 'value' => $conversion['checkout_starts'] ?? 0],
          ['label' => __('console/visit.order_placed'), 'value' => $conversion['order_placed'] ?? 0],
          ['label' => __('console/visit.payment_completed'), 'value' => $conversion['payment_completed'] ?? 0],
        ];
        $funnelMax = max(1, $conversion['product_views'] ?? 0);
      @endphp
      @foreach($funnel as $step)
        @php $percent = min(100, round((($step['value'] ?? 0) / $funnelMax) * 100, 1)); @endphp
        <div class="mb-3">
          <div class="d-flex justify-content-between small mb-1">
            <span>{{ $step['label'] }}</span>
            <span class="fw-bold">{{ $step['value'] }}</span>
          </div>
          <div class="progress" style="height: 18px;">
            <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%;"
                 aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">{{ $percent }}%</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  {{-- 转化率 --}}
  <h6 class="text-muted mb-2">{{ __('console/visit.conversion_rates') }}</h6>
  <div class="row g-3">
    @php
      $rates = $conversion['rates'] ?? [];
      $rateCards = [
        ['label' => __('console/visit.cart_to_checkout'), 'value' => ($rates['cart_to_checkout'] ?? 0) . '%'],
        ['label' => __('console/visit.checkout_to_order'), 'value' => ($rates['checkout_to_order'] ?? 0) . '%'],
        ['label' => __('console/visit.order_to_payment'), 'value' => ($rates['order_to_payment'] ?? 0) . '%'],
        ['label' => __('console/visit.overall_conversion'), 'value' => ($rates['overall_conversion'] ?? 0) . '%'],
      ];
    @endphp
    @foreach($rateCards as $card)
      <div class="col-6 col-md-3">
        <div class="card h-100">
          <div class="card-body text-center">
            <div class="fs-4 fw-bold text-primary">{{ $card['value'] }}</div>
            <div class="text-muted small">{{ $card['label'] }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endsection

@push('footer')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const aggregateBtn = document.getElementById('btn-aggregate');
      if (!aggregateBtn) {
        return;
      }

      aggregateBtn.addEventListener('click', function () {
        const originalHtml = aggregateBtn.innerHTML;
        aggregateBtn.disabled = true;
        aggregateBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> {{ __('console/common.loading') }}';

        const date = document.getElementById('date').value;

        fetch('{{ console_route('visits.aggregate') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ date: date })
        }).then(response => response.json().then(data => {
          if (response.ok && data.success) {
            inno.msg(data.message || '{{ __('console/visit.aggregate_done') }}');
            setTimeout(() => window.location.reload(), 1000);
          } else {
            inno.msg(data.message || data.error || '{{ __('console/visit.aggregate_failed') }}');
            aggregateBtn.disabled = false;
            aggregateBtn.innerHTML = originalHtml;
          }
        })).catch(() => {
          inno.msg('{{ __('console/visit.aggregate_failed') }}');
          aggregateBtn.disabled = false;
          aggregateBtn.innerHTML = originalHtml;
        });
      });
    });
  </script>
@endpush
