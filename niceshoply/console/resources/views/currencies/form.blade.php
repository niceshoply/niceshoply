@extends('console::layouts.app')

@section('title', __('console/menu.currencies'))

<x-console::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/menu.currencies') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form"
      action="{{ $currency->id ? console_route('currencies.update', [$currency->id]) : console_route('currencies.store') }}"
      method="POST">
      @csrf
      @method($currency->id ? 'PUT' : 'POST')

      <x-common-form-input title="{{ __('console/common.name') }}" name="name" value="{{ old('name', $currency->name) }}" required />
      <x-common-form-input title="{{ __('console/currency.code') }}" name="code" value="{{ old('code', $currency->code) }}" required />
      <x-common-form-input title="{{ __('console/currency.symbol_left') }}" name="symbol_left" value="{{ old('symbol_left', $currency->symbol_left) }}" />
      <x-common-form-input title="{{ __('console/currency.symbol_right') }}" name="symbol_right" value="{{ old('symbol_right', $currency->symbol_right) }}" />
      <x-common-form-input title="{{ __('console/currency.decimal_place') }}" name="decimal_place" value="{{ old('decimal_place', $currency->decimal_place) }}" required />
      <x-common-form-input title="{{ __('console/currency.value') }}" name="value" value="{{ old('value', $currency->value) }}" />
      <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active" :value="old('active', $page->active ?? true)" placeholder="{{ __('console/common.whether_enable') }}"/>

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script></script>
@endpush