@extends('console::layouts.app')

@section('title', __('StockNotify::common.menu'))

@section('content')
  <div class="alert alert-info py-2">{{ __('StockNotify::common.cron_hint') }}</div>

  <div class="row mb-3">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <div class="h3 mb-0">{{ $pendingCount }}</div>
          <div class="text-muted small">{{ __('StockNotify::common.pending_count') }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-9 d-flex align-items-center">
      <button id="btn-scan" class="btn btn-primary">{{ __('StockNotify::common.scan_now') }}</button>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('StockNotify::common.customer_id') }}</th>
            <th>{{ __('StockNotify::common.sku_code') }}</th>
            <th>{{ __('StockNotify::common.type') }}</th>
            <th>{{ __('StockNotify::common.target_price') }}</th>
            <th>{{ __('StockNotify::common.status') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($subscriptions as $sub)
            <tr>
              <td>{{ $sub->id }}</td>
              <td>{{ $sub->customer_id }}</td>
              <td><code>{{ $sub->sku_code }}</code></td>
              <td>{{ __('StockNotify::common.type_'.$sub->type) }}</td>
              <td>{{ $sub->type === 'price_drop' ? currency_format($sub->target_price) : '-' }}</td>
              <td><span class="badge bg-secondary">{{ __('StockNotify::common.st_'.$sub->status) }}</span></td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('StockNotify::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $subscriptions->links() }}</div>
    </div>
  </div>

  <script>
    document.getElementById('btn-scan').addEventListener('click', function () {
      const btn = this;
      const original = btn.innerText;
      btn.disabled = true;
      btn.innerText = '{{ __('StockNotify::common.scanning') }}';
      fetch('{{ console_route('stock_notify.scan') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
      }).then(r => r.json()).then(function (res) {
        alert(res.message || 'done');
        location.reload();
      }).finally(function () {
        btn.disabled = false;
        btn.innerText = original;
      });
    });
  </script>
@endsection
