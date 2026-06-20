@extends('console::layouts.app')

@section('title', __('VirtualGoods::common.tab_deliveries'))

@section('content')
  <div class="mb-3">
    <a href="{{ console_route('virtual_goods.index') }}" class="btn btn-outline-secondary btn-sm">&laquo; {{ __('VirtualGoods::common.menu') }}</a>
  </div>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered align-middle mb-0">
        <thead>
        <tr>
          <th>#</th>
          <th>{{ __('VirtualGoods::common.order') }}</th>
          <th>{{ __('VirtualGoods::common.customer') }}</th>
          <th>SKU</th>
          <th>{{ __('VirtualGoods::common.content') }}</th>
          <th>{{ __('VirtualGoods::common.created_at') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($deliveries as $d)
          <tr>
            <td>{{ $d->id }}</td>
            <td>{{ $d->order_id }}</td>
            <td>{{ $d->customer_id }}</td>
            <td>{{ $d->product_sku }}</td>
            <td><code style="white-space:pre-wrap">{{ $d->content }}</code></td>
            <td>{{ optional($d->created_at)->format('Y-m-d H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">{{ __('VirtualGoods::common.no_data') }}</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $deliveries->links() }}</div>
  </div>
@endsection
