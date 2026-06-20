@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.analytics_order'))

@push('header')
  <script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')

  <div class="row">

    <div class="col-12">
      <x-console-chart-line id="order_quantity_month" :labels="$order_quantity_latest_month['period']" :title="__('console/dashboard.order_trends')"
                          :data="$order_quantity_latest_month['totals']"></x-console-chart-line>
    </div>

    <div class="col-12">
      <x-console-chart-line id="order_total_month" :labels="$order_total_latest_month['period']" :title="__('console/analytics.total_trends')"
                          :data="$order_total_latest_month['totals']"></x-console-chart-line>
    </div>

    <div class="col-12 col-md-6 mb-3">
      <x-console-chart-line id="order_quantity_week" :labels="$order_quantity_latest_week['period']" :title="__('console/dashboard.order_trends')"
                          :data="$order_quantity_latest_week['totals']"></x-console-chart-line>

      <x-console-chart-line id="order_total_week" :labels="$order_total_latest_week['period']" :title="__('console/analytics.total_trends')"
                          :data="$order_total_latest_week['totals']"></x-console-chart-line>

    </div>
    <div class="col-12 col-md-6 mb-3">
      <div class="card top-sale-products">
        <div class="card-header">{{ __('console/dashboard.top_products') }}</div>
        <div class="card-body pb-0">
          @if ($top_sale_products)
            <table class="table table-last-no-border align-middle mt-n3 mb-0">
              <tbody>
              @foreach($top_sale_products as $product)
                <tr>
                  <td class="text-center">
                    @if ($loop->iteration <= 3)
                      <img src="{{ asset('images/icons/grade-'. $loop->iteration .'.svg') }}" alt="{{ $product['name'] }}" class="img-fluid wh-30">
                    @else
                      <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                    @endif
                  </td>
                  <td>
                    <a class="d-flex align-items-center text-dark text-decoration-none" href="{{ console_route('products.edit', $product['product_id']) }}">
                      <div class="wh-30 rounded-circle overflow-hidden border border-1 me-2"><img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="img-fluid"></div>
                      {{ $product['summary'] }}
                    </a>
                  </td>
                  <td class="text-center">{{ $product['order_count'] }}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          @else
            <x-common-no-data :width="240" />
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
