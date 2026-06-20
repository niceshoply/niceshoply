@extends('console::layouts.app')
@section('body-class', 'page-coupon-usage')

@section('title', __('console/coupon.usages').' - '.$coupon->code)

@section('page-title-right')
<a href="{{ console_route('coupons.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>
  {{ __('console/coupon.back') }}</a>
@endsection

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    @if ($usages->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/coupon.order_id') }}</td>
            <td>{{ __('console/coupon.customer_id') }}</td>
            <td>{{ __('console/coupon.discount_amount') }}</td>
            <td>{{ __('console/coupon.used_at') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($usages as $usage)
          <tr>
            <td>{{ $usage->id }}</td>
            <td>{{ $usage->order_id }}</td>
            <td>{{ $usage->customer_id ?: '-' }}</td>
            <td>{{ currency_format($usage->discount_amount) }}</td>
            <td>{{ optional($usage->used_at)->format('Y-m-d H:i:s') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $usages->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
