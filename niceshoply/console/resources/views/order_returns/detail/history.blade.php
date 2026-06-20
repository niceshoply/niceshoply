{{-- Return Processing History --}}
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/order_return.history') }}</h5>
  </div>
  <div class="card-body">
    @if ($order_return->histories->count())
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th>{{ __('front/return.status') }}</th>
            <th>{{ __('console/order_return.comment') }}</th>
            <th>{{ __('console/order_return.notify') }}</th>
            <th>{{ __('console/order_return.date_time') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order_return->histories as $history)
            <tr>
              <td><span class="badge bg-secondary">{{ $history->status_format }}</span></td>
              <td>{{ $history->comment }}</td>
              <td>{{ $history->notify ? __('front/common.yes') : __('front/common.no') }}</td>
              <td><span class="text-muted small">{{ $history->created_at }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <p class="text-muted mb-0">{{ __('console/order_return.no_history') }}</p>
    @endif
  </div>
</div>

@hookinsert('console.order_returns.detail.history.after', $order_return)
