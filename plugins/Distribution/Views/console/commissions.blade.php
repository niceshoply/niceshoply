@extends('console::layouts.app')

@section('title', __('Distribution::common.commissions'))

@section('page-title-right')
  <a href="{{ console_route('distribution.distributors') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-people"></i> {{ __('Distribution::common.distributors') }}
  </a>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Distribution::common.order_id') }}</th>
            <th>{{ __('Distribution::common.buyer') }}</th>
            <th>{{ __('Distribution::common.distributor') }}</th>
            <th>{{ __('Distribution::common.level') }}</th>
            <th>{{ __('Distribution::common.rate') }}</th>
            <th>{{ __('Distribution::common.amount') }}</th>
            <th>{{ __('Distribution::common.status') }}</th>
            <th class="text-end">{{ __('Distribution::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($commissions as $c)
            <tr>
              <td>{{ $c->id }}</td>
              <td>{{ $c->order_id }}</td>
              <td>{{ $c->buyer_customer_id }}</td>
              <td>{{ $c->distributor_customer_id }}</td>
              <td>L{{ $c->level }}</td>
              <td>{{ $c->rate }}%</td>
              <td><strong>{{ currency_format($c->amount) }}</strong></td>
              <td>
                @switch($c->status)
                  @case('settled')<span class="badge bg-success">{{ __('Distribution::common.status_settled') }}</span>@break
                  @case('cancelled')<span class="badge bg-secondary">{{ __('Distribution::common.status_cancelled') }}</span>@break
                  @default<span class="badge bg-warning text-dark">{{ __('Distribution::common.status_pending') }}</span>
                @endswitch
              </td>
              <td class="text-end">
                @if($c->status === 'pending')
                  <button class="btn btn-sm btn-outline-success btn-settle" data-id="{{ $c->id }}">{{ __('Distribution::common.settle') }}</button>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-muted py-4">{{ __('Distribution::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $commissions->links() }}
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-settle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm(@json(__('Distribution::common.confirm_settle')))) return;
        fetch('{{ console_route('distribution.commissions') }}/' + this.dataset.id + '/settle', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
