@extends('console::layouts.app')

@section('title', __('VirtualGoods::common.tab_cards'))

@section('content')
  <div class="mb-3">
    <a href="{{ console_route('virtual_goods.index') }}" class="btn btn-outline-secondary btn-sm">&laquo; {{ __('VirtualGoods::common.menu') }}</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-auto">
      <input name="product_sku" value="{{ $sku }}" class="form-control" placeholder="SKU">
    </div>
    <div class="col-auto"><button class="btn btn-primary">{{ __('VirtualGoods::common.view_cards') }}</button></div>
  </form>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-bordered align-middle mb-0">
        <thead>
        <tr>
          <th>#</th>
          <th>SKU</th>
          <th>{{ __('VirtualGoods::common.content') }}</th>
          <th>{{ __('VirtualGoods::common.status') }}</th>
          <th>{{ __('VirtualGoods::common.order') }}</th>
          <th>{{ __('VirtualGoods::common.used_at') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($cards as $c)
          <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->product_sku }}</td>
            <td><code>{{ $c->content }}</code></td>
            <td>
              @if($c->status === 'unused')<span class="badge bg-success">{{ __('VirtualGoods::common.status_unused') }}</span>
              @else<span class="badge bg-secondary">{{ __('VirtualGoods::common.status_used') }}</span>@endif
            </td>
            <td>{{ $c->order_id }}</td>
            <td>{{ optional($c->used_at)->format('Y-m-d H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">{{ __('VirtualGoods::common.no_data') }}</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">{{ $cards->appends(['product_sku' => $sku])->links() }}</div>
  </div>
@endsection
