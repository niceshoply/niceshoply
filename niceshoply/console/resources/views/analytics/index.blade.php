@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.analytics'))

@push('header')
  <script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')

  <div class="row">
    <div class="col-12">

      <x-console-chart-line id="order" :labels="$order_latest_week['period']" :title="__('console/dashboard.order_trends')"
                          :data="$order_latest_week['totals']"></x-console-chart-line>

      <x-console-chart-line id="product" :labels="$product_latest_week['period']" :title="__('console/analytics.product_trends')"
                          :data="$product_latest_week['totals']"></x-console-chart-line>

      <x-console-chart-line id="customer" :labels="$customer_latest_week['period']" :title="__('console/analytics.customer_trends')"
                          :data="$customer_latest_week['totals']"></x-console-chart-line>
    </div>
  </div>
@endsection
