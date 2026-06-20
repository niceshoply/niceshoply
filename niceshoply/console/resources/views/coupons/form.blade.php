@extends('console::layouts.app')

@section('title', __('console/coupon.coupons'))

<x-console::form.right-btns />

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $coupon->id ? console_route('coupons.update', [$coupon->id]) : console_route('coupons.store') }}" method="POST">
  @csrf
  @method($coupon->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12 col-md-9">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('console/common.basic_info') }}</h5>
        </div>
        <div class="card-body">
          <x-common-form-input title="{{ __('console/coupon.code') }}" name="code"
            :value="old('code', $coupon->code ?? '')" placeholder="{{ __('console/coupon.code_hint') }}" />

          <x-common-form-select title="{{ __('console/coupon.type') }}" name="type"
            :options="$typeOptions" key="code" label="label" value="{{ old('type', $coupon->type ?? 'fixed') }}" />

          <x-common-form-input title="{{ __('console/coupon.value') }}" name="value"
            :value="old('value', $coupon->value ?? 0)" placeholder="{{ __('console/coupon.value_hint') }}" />

          <x-common-form-input title="{{ __('console/coupon.min_amount') }}" name="min_amount"
            :value="old('min_amount', $coupon->min_amount ?? 0)" placeholder="{{ __('console/coupon.min_amount_hint') }}" />
        </div>
      </div>

      @unless($coupon->id)
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('console/coupon.batch') }}</h5>
        </div>
        <div class="card-body">
          <x-common-form-input title="{{ __('console/coupon.batch_count') }}" name="batch_count"
            :value="old('batch_count', 0)" placeholder="{{ __('console/coupon.batch_count_hint') }}" />
          <x-common-form-input title="{{ __('console/coupon.batch_prefix') }}" name="batch_prefix"
            :value="old('batch_prefix', '')" placeholder="{{ __('console/coupon.batch_prefix_hint') }}" />
        </div>
      </div>
      @endunless
    </div>

    <div class="col-12 col-md-3 ps-md-0">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active"
            :value="old('active', $coupon->active ?? true)" />

          <x-common-form-input title="{{ __('console/coupon.total_limit') }}" name="total_limit"
            :value="old('total_limit', $coupon->total_limit ?? 0)" />

          <x-common-form-input title="{{ __('console/coupon.per_customer_limit') }}" name="per_customer_limit"
            :value="old('per_customer_limit', $coupon->per_customer_limit ?? 1)" />

          <x-common-form-date title="{{ __('console/coupon.starts_at') }}" name="starts_at"
            :value="old('starts_at', optional($coupon->starts_at)->format('Y-m-d H:i:s'))" />

          <x-common-form-date title="{{ __('console/coupon.ends_at') }}" name="ends_at"
            :value="old('ends_at', optional($coupon->ends_at)->format('Y-m-d H:i:s'))" />
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection
