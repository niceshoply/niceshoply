@extends('console::layouts.app')

@section('title', __('Subscription::common.menu'))

@section('content')
  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <div class="card text-center"><div class="card-body">
        <div class="fs-4 fw-bold">{{ $stats['active'] }}</div>
        <div class="text-muted small">{{ __('Subscription::common.stat_active') }}</div>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center"><div class="card-body">
        <div class="fs-4 fw-bold text-warning">{{ $stats['due'] }}</div>
        <div class="text-muted small">{{ __('Subscription::common.stat_due') }}</div>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center"><div class="card-body">
        <div class="fs-4 fw-bold">{{ $stats['paused'] }}</div>
        <div class="text-muted small">{{ __('Subscription::common.stat_paused') }}</div>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-center"><div class="card-body">
        <div class="fs-4 fw-bold text-muted">{{ $stats['cancelled'] }}</div>
        <div class="text-muted small">{{ __('Subscription::common.stat_cancelled') }}</div>
      </div></div>
    </div>
  </div>

  <div class="mb-3">
    <button id="btn-run" class="btn btn-primary"><i class="bi bi-play-circle"></i> {{ __('Subscription::common.run_now') }}</button>
  </div>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered align-middle mb-0">
        <thead>
        <tr>
          <th>{{ __('Subscription::common.id') }}</th>
          <th>{{ __('Subscription::common.customer') }}</th>
          <th>{{ __('Subscription::common.product') }}</th>
          <th>{{ __('Subscription::common.qty') }}</th>
          <th>{{ __('Subscription::common.interval') }}</th>
          <th>{{ __('Subscription::common.payment_mode') }}</th>
          <th>{{ __('Subscription::common.status') }}</th>
          <th>{{ __('Subscription::common.next_run') }}</th>
          <th>{{ __('Subscription::common.cycles') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($subscriptions as $s)
          <tr>
            <td>{{ $s->id }}</td>
            <td>{{ $s->customer_id }}</td>
            <td>{{ $s->name }}<div class="text-muted small">{{ $s->product_sku }}</div></td>
            <td>{{ $s->quantity }}</td>
            <td>{{ __('Subscription::common.every') }} {{ $s->interval_count }} {{ __('Subscription::common.unit_'.$s->interval_unit) }}</td>
            <td>
              @if($s->payment_mode === 'auto_balance')
                <span class="badge bg-info">{{ __('Subscription::common.mode_auto_balance') }}</span>
              @else
                <span class="badge bg-secondary">{{ __('Subscription::common.mode_reminder') }}</span>
              @endif
            </td>
            <td>
              @if($s->status === 'active')<span class="badge bg-success">{{ __('Subscription::common.stat_active') }}</span>
              @elseif($s->status === 'paused')<span class="badge bg-warning text-dark">{{ __('Subscription::common.stat_paused') }}</span>
              @else<span class="badge bg-secondary">{{ __('Subscription::common.stat_cancelled') }}</span>@endif
            </td>
            <td>{{ optional($s->next_run_at)->format('Y-m-d H:i') }}</td>
            <td>{{ $s->cycles_done }}</td>
          </tr>
        @empty
          <tr><td colspan="9" class="text-center text-muted py-4">{{ __('Subscription::common.no_data') }}</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $subscriptions->links() }}</div>
  </div>

  <script>
    document.getElementById('btn-run').addEventListener('click', function () {
      const btn = this;
      btn.disabled = true;
      fetch('{{ console_route('subscription.run') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
      }).then(r => r.json()).then(function (res) {
        alert(res.message || 'done');
        location.reload();
      }).catch(function () { btn.disabled = false; });
    });
  </script>
@endsection
