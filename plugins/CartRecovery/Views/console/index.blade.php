@extends('console::layouts.app')

@section('title', __('CartRecovery::common.menu'))

@section('content')
  <div class="alert alert-info py-2">{{ __('CartRecovery::common.cron_hint') }}</div>

  <div class="row mb-3">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <div class="h3 mb-0">{{ $totalSent }}</div>
          <div class="text-muted small">{{ __('CartRecovery::common.total_sent') }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-9 d-flex align-items-center">
      <button id="btn-scan" class="btn btn-primary">{{ __('CartRecovery::common.scan_now') }}</button>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('CartRecovery::common.customer_id') }}</th>
            <th>{{ __('CartRecovery::common.item_count') }}</th>
            <th>{{ __('CartRecovery::common.channel') }}</th>
            <th>{{ __('CartRecovery::common.sent_at') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($logs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ $log->customer_id }}</td>
              <td>{{ $log->item_count }}</td>
              <td>{{ $log->channel }}</td>
              <td>{{ optional($log->sent_at)->format('Y-m-d H:i') }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">{{ __('CartRecovery::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $logs->links() }}</div>
    </div>
  </div>

  <script>
    document.getElementById('btn-scan').addEventListener('click', function () {
      const btn = this;
      const original = btn.innerText;
      btn.disabled = true;
      btn.innerText = '{{ __('CartRecovery::common.scanning') }}';
      fetch('{{ console_route('cart_recovery.scan') }}', {
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
