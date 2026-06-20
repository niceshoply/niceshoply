@extends('console::layouts.app')
@section('body-class', 'page-abandoned-cart')

@section('title', __('console/abandoned_cart.title'))

@section('content')
<div class="card h-min-600">
  <div class="card-body">

    <form class="row g-3 mb-4" method="GET" action="{{ console_route('abandoned_carts.index') }}">
      <div class="col-md-3">
        <label class="form-label">{{ __('console/abandoned_cart.start_date') }}</label>
        <input type="date" name="start" class="form-control" value="{{ $start }}">
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('console/abandoned_cart.end_date') }}</label>
        <input type="date" name="end" class="form-control" value="{{ $end }}">
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary">{{ __('console/common.search') }}</button>
      </div>
    </form>

    <div class="row mb-4">
      <div class="col-md-3">
        <div class="border rounded p-3">
          <div class="text-muted small">{{ __('console/abandoned_cart.total_records') }}</div>
          <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="border rounded p-3">
          <div class="text-muted small">{{ __('console/abandoned_cart.reminded') }}</div>
          <div class="fs-4 fw-bold">{{ $stats['reminded'] }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="border rounded p-3">
          <div class="text-muted small">{{ __('console/abandoned_cart.converted_count') }}</div>
          <div class="fs-4 fw-bold text-success">{{ $stats['converted'] }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="border rounded p-3">
          <div class="text-muted small">{{ __('console/abandoned_cart.conversion_rate') }}</div>
          <div class="fs-4 fw-bold text-primary">{{ $stats['rate'] }}%</div>
        </div>
      </div>
    </div>

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('abandoned_carts.index')" />

    @if ($abandonedCarts->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/abandoned_cart.email') }}</td>
            <td>{{ __('console/abandoned_cart.cart_total') }}</td>
            <td>{{ __('console/abandoned_cart.item_count') }}</td>
            <td>{{ __('console/abandoned_cart.reminder_count') }}</td>
            <td>{{ __('console/abandoned_cart.coupon_code') }}</td>
            <td>{{ __('console/abandoned_cart.converted') }}</td>
            <td>{{ __('console/common.created_at') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($abandonedCarts as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>
              @if($item->customer)
              <a href="{{ console_route('customers.edit', [$item->customer_id]) }}" class="text-decoration-none">{{ $item->email }}</a>
              @else
              {{ $item->email ?: '-' }}
              @endif
            </td>
            <td>{{ currency_format($item->cart_total, $item->currency_code) }}</td>
            <td>{{ count($item->cart_snapshot ?? []) }}</td>
            <td>{{ $item->reminder_count }}</td>
            <td>{{ $item->coupon_code ?: '-' }}</td>
            <td>
              @if($item->converted)
              <span class="badge bg-success">{{ __('console/common.yes') }}</span>
              @if($item->converted_order_id)
              <a href="{{ console_route('orders.show', [$item->converted_order_id]) }}" class="small">#{{ $item->converted_order_id }}</a>
              @endif
              @else
              <span class="badge bg-secondary">{{ __('console/common.no') }}</span>
              @endif
            </td>
            <td>{{ $item->created_at }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $abandonedCarts->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
