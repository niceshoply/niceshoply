@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.orders'))
@section('page-eyebrow', __('console/menu.orders'))
@section('page-subtitle', __('console/order.list_subtitle'))

@section('page-title-right')
  @hookinsert('console.orders.index.title.right')
@endsection

@section('content')
  @isset($stats)
    <div class="stat-strip">
      <div class="stat-card">
        <div class="stat-label">{{ __('console/order.stat_total') }}</div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
        <div class="stat-trend muted">{{ __('console/order.status') }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">{{ __('console/order.stat_unpaid') }}</div>
        <div class="stat-value">{{ number_format($stats['unpaid']) }}</div>
        <div class="stat-trend down">{{ __('console/order.unpaid') }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">{{ __('console/order.stat_paid') }}</div>
        <div class="stat-value">{{ number_format($stats['paid']) }}</div>
        <div class="stat-trend">{{ __('console/order.paid') }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">{{ __('console/order.stat_shipping') }}</div>
        <div class="stat-value">{{ number_format($stats['shipping']) }}</div>
        <div class="stat-trend muted">{{ __('console/order.shipment_status') }}</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">{{ __('console/order.stat_cancelled') }}</div>
        <div class="stat-value">{{ number_format($stats['cancelled']) }}</div>
        <div class="stat-trend muted">{{ __('console/order.cancelled') }}</div>
      </div>
    </div>
  @endisset

  <div class="card h-min-600">
    <div class="card-body">
      <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('orders.index')" :export="true" />
      @hookinsert('console.orders.index.criteria.after')

      @if ($orders->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
               @hookinsert('console.orders.index.header.top')
                <td>{{ __('console/common.id') }}</td>
                <td>{{ __('console/order.number') }}</td>
                <td>{{ __('console/order.order_items') }}</td>
                <td>{{ __('console/order.customer_name') }}</td>
                <td>{{ __('console/order.shipping_method_name') }}</td>
                <td>{{ __('console/order.billing_method_name') }}</td>
                <td>{{ __('console/order.total') }}</td>
                <td>{{ __('console/order.status') }}</td>
                @hookinsert('console.orders.index.header.extra')
                <td>{{ __('console/order.created_at') }}</td>
                <td>{{ __('console/common.actions') }}</td>
              </tr>
            </thead>
            <tbody>
              @foreach ($orders as $item)
                <tr>
                 @hookinsert('console.orders.index.row.top', $item)
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->number }} {{ $item->id == $item->parent_id ? 'M' : '' }}</td>
                  <td>
                    <div class="d-flex">
                      @foreach ($item->items->take(5) as $product)
                        <div class="wh-30 overflow-hidden border border-1 me-1">
                          <img src="{{ image_resize($product->image) }}" alt="{{ $product->name }}" class="img-fluid">
                        </div>
                      @endforeach
                    </div>
                  </td>
                  <td>
                    @if ($item->customer_id > 0)
                      <a href="{{ console_route('customers.edit', $item->customer_id) }}" class="text-decoration-none"
                      target="_blank">
                        {{ $item->customer_name }}
                      </a>
                    @else
                      {{ $item->customer_name }}
                    @endif
                  </td>
                  <td>{{ $item->shipping_method_name }}</td>
                  <td>{{ $item->billing_method_name }}</td>
                  <td>{{ $item->total_format }}</td>
                  <td><span class="badge bg-{{ $item->status_color }}">{{ $item->status_format }}</span></td>
                  @hookinsert('console.orders.index.row.extra', $item)
                  <td>{{ $item->created_at }}</td>
                  <td>
                    <a href="{{ console_route('orders.show', [$item->id]) }}"
                      class="btn btn-sm btn-outline-primary">{{ __('console/common.view') }}</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        {{ $orders->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data />
      @endif
    </div>
  </div>
@endsection

@push('footer')
    @hookinsert('console.orders.footer.script.bottom')
@endpush
