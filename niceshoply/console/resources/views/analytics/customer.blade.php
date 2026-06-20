@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.analytics_customer'))

@push('header')
  <script src="{{ asset('vendor/chart/chart.min.js') }}"></script>
@endpush

@section('content')

  <div class="row">
    <div class="col-12">
      <x-console-chart-line id="customer" :labels="$customer_latest_week['period']" :title="__('console/analytics.customer_trends')"
                          :data="$customer_latest_week['totals']"></x-console-chart-line>
    </div>
  </div>

  <div class="row d-flex ">
    <div class="col-6">
      <x-console-chart-pie id="customer_source" :labels="$customer_source['labels']" :title="__('console/analytics.customer_source')"
                         :data="$customer_source['data']"></x-console-chart-pie>
    </div>

   @hookinsert('console.analytics.customer.bottom')

  </div>
@endsection

