@extends('console::layouts.app')

@section('title', __('DashboardBi::common.title'))

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('DashboardBi::common.title') }}</h5>
    <select id="range" class="form-select w-auto">
      <option value="7">{{ __('DashboardBi::common.last_7') }}</option>
      <option value="30" selected>{{ __('DashboardBi::common.last_30') }}</option>
      <option value="90">{{ __('DashboardBi::common.last_90') }}</option>
    </select>
  </div>

  <div class="row g-3 mb-3" id="kpis">
    @foreach(['revenue'=>'revenue_format','order_count'=>'order_count','paid_count'=>'paid_count','aov'=>'aov_format','new_customers'=>'new_customers'] as $key => $field)
      <div class="col">
        <div class="card text-center"><div class="card-body">
          <div class="text-muted small">{{ __('DashboardBi::common.'.$key) }}</div>
          <div class="fs-4 fw-bold" data-kpi="{{ $field }}">-</div>
        </div></div>
      </div>
    @endforeach
  </div>

  <div class="row g-3">
    <div class="col-lg-8">
      <div class="card"><div class="card-header">{{ __('DashboardBi::common.trend_title') }}</div>
        <div class="card-body"><canvas id="trendChart" height="110"></canvas></div></div>
    </div>
    <div class="col-lg-4">
      <div class="card"><div class="card-header">{{ __('DashboardBi::common.status_title') }}</div>
        <div class="card-body"><canvas id="statusChart" height="200"></canvas></div></div>
    </div>
  </div>

  <div class="card mt-3"><div class="card-header">{{ __('DashboardBi::common.top_title') }}</div>
    <div class="card-body table-responsive">
      <table class="table table-bordered align-middle mb-0">
        <thead><tr><th>#</th><th>{{ __('DashboardBi::common.product') }}</th><th>{{ __('DashboardBi::common.qty') }}</th><th>{{ __('DashboardBi::common.amount') }}</th></tr></thead>
        <tbody id="top-body"><tr><td colspan="4" class="text-center text-muted py-4">{{ __('DashboardBi::common.no_data') }}</td></tr></tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
  <script>
    const statusLabels = {
      unpaid: '{{ __('DashboardBi::common.status_unpaid') }}',
      paid: '{{ __('DashboardBi::common.status_paid') }}',
      completed: '{{ __('DashboardBi::common.status_completed') }}',
      cancelled: '{{ __('DashboardBi::common.status_cancelled') }}'
    };
    let trendChart, statusChart;

    function render(d) {
      document.querySelectorAll('[data-kpi]').forEach(el => {
        el.textContent = d[el.dataset.kpi] ?? '-';
      });

      const ctx1 = document.getElementById('trendChart');
      const cfg1 = {
        type: 'line',
        data: { labels: d.trend.labels, datasets: [
          { label: '{{ __('DashboardBi::common.revenue') }}', data: d.trend.revenue, borderColor: '#0d6efd', yAxisID: 'y', tension: .3 },
          { label: '{{ __('DashboardBi::common.order_count') }}', data: d.trend.orders, borderColor: '#198754', yAxisID: 'y1', tension: .3 }
        ]},
        options: { scales: { y: { position: 'left' }, y1: { position: 'right', grid: { drawOnChartArea: false } } } }
      };
      if (trendChart) trendChart.destroy();
      trendChart = new Chart(ctx1, cfg1);

      const labels = Object.keys(d.status_dist).map(k => statusLabels[k] || k);
      const values = Object.values(d.status_dist);
      const cfg2 = { type: 'doughnut', data: { labels, datasets: [{ data: values,
        backgroundColor: ['#6c757d','#0d6efd','#198754','#dc3545','#ffc107','#0dcaf0'] }] } };
      if (statusChart) statusChart.destroy();
      statusChart = new Chart(document.getElementById('statusChart'), cfg2);

      const tb = document.getElementById('top-body');
      if (!d.top_products.length) {
        tb.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">{{ __('DashboardBi::common.no_data') }}</td></tr>';
      } else {
        tb.innerHTML = d.top_products.map((p, i) =>
          `<tr><td>${i+1}</td><td>${p.name ?? ''}</td><td>${p.qty}</td><td>${p.amount}</td></tr>`).join('');
      }
    }

    function load() {
      const days = document.getElementById('range').value;
      fetch('{{ console_route('dashboard_bi.data') }}?days=' + days, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json()).then(res => { if (res.success) render(res.data); });
    }
    document.getElementById('range').addEventListener('change', load);
    load();
  </script>
@endsection
