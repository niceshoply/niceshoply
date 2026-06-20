@extends('console::layouts.app')

@section('title', __('Recharge::common.menu_records'))

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Recharge::common.order_id') }}</th>
            <th>{{ __('Recharge::common.customer_id') }}</th>
            <th>{{ __('Recharge::common.amount') }}</th>
            <th>{{ __('Recharge::common.bonus') }}</th>
            <th>{{ __('Recharge::common.status') }}</th>
            <th>{{ __('Recharge::common.credited_at') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($records as $r)
            <tr>
              <td>{{ $r->id }}</td>
              <td>{{ $r->order_id }}</td>
              <td>{{ $r->customer_id }}</td>
              <td>{{ currency_format($r->amount) }}</td>
              <td>{{ currency_format($r->bonus) }}</td>
              <td>
                @if($r->status === 'credited')<span class="badge bg-success">{{ __('Recharge::common.st_credited') }}</span>
                @else<span class="badge bg-warning text-dark">{{ __('Recharge::common.st_pending') }}</span>@endif
              </td>
              <td>{{ optional($r->credited_at)->format('Y-m-d H:i') }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('Recharge::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $records->links() }}</div>
    </div>
  </div>
@endsection
