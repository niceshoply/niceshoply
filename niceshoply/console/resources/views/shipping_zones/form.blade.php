@extends('console::layouts.app')

@section('title', __('console/shipping.zones'))

<x-console::form.right-btns />

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $zone->id ? console_route('shipping_zones.update', [$zone->id]) : console_route('shipping_zones.store') }}" method="POST">
  @csrf
  @method($zone->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12 col-md-9">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('console/common.basic_info') }}</h5>
        </div>
        <div class="card-body">
          <x-common-form-input title="{{ __('console/shipping.zone_name') }}" name="name"
            :value="old('name', $zone->name ?? '')" required />

          <x-common-form-input title="{{ __('console/shipping.country_ids') }}" name="country_ids"
            :value="old('country_ids', implode(',', $zone->country_ids ?? []))"
            description="{{ __('console/shipping.country_ids_hint') }}" />

          <x-common-form-input title="{{ __('console/shipping.state_ids') }}" name="state_ids"
            :value="old('state_ids', implode(',', $zone->state_ids ?? []))"
            description="{{ __('console/shipping.state_ids_hint') }}" />
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3 ps-md-0">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active"
            :value="old('active', $zone->active ?? true)" />
          <x-common-form-input title="{{ __('console/shipping.priority') }}" name="priority"
            :value="old('priority', $zone->priority ?? 0)" />
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection
