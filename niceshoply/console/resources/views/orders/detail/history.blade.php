{{-- Order History --}}
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/order.history') }}</h5>
  </div>
  <div class="card-body">
    <table class="table table-response align-middle">
      <thead>
        <tr>
          <th>{{ __('console/order.status') }}</th>
          <th>{{ __('console/order.comment') }}</th>
          <th>{{ __('console/order.date_time') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($order->histories->sortByDesc('id') as $history)
          <tr>
            <td>{{ $history->status }}</td>
            <td>{{ $history->comment }}</td>
            <td>{{ $history->created_at }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@hookinsert('console.orders.detail.history.after')
