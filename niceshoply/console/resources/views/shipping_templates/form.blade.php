@extends('console::layouts.app')

@section('title', __('console/shipping.templates'))

<x-console::form.right-btns />

@php
  $zoneOptions = [['code' => '', 'label' => __('console/shipping.all_zones')]];
  foreach ($zones as $zone) {
      $zoneOptions[] = ['code' => $zone->id, 'label' => $zone->name];
  }
  $rulesValue = old('rules', $template->rules ? json_encode($template->rules, JSON_UNESCAPED_UNICODE) : '');
@endphp

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $template->id ? console_route('shipping_templates.update', [$template->id]) : console_route('shipping_templates.store') }}" method="POST">
  @csrf
  @method($template->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12 col-md-9">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('console/common.basic_info') }}</h5>
        </div>
        <div class="card-body">
          <x-common-form-input title="{{ __('console/shipping.template_name') }}" name="name"
            :value="old('name', $template->name ?? '')" required />

          <x-common-form-select title="{{ __('console/shipping.zone') }}" name="zone_id"
            :options="$zoneOptions" key="code" label="label" value="{{ old('zone_id', $template->zone_id ?? '') }}" />

          <x-common-form-select title="{{ __('console/shipping.calc_type') }}" name="calc_type"
            :options="$calcTypeOptions" key="code" label="label" value="{{ old('calc_type', $template->calc_type ?? 'flat') }}" />

          <x-common-form-textarea title="{{ __('console/shipping.rules') }}" name="rules"
            :value="$rulesValue" description="{{ __('console/shipping.rules_hint') }}" />
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3 ps-md-0">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active"
            :value="old('active', $template->active ?? true)" />

          <x-common-form-input title="{{ __('console/shipping.free_threshold') }}" name="free_threshold"
            :value="old('free_threshold', $template->free_threshold ?? 0)"
            description="{{ __('console/shipping.free_threshold_hint') }}" />

          <x-common-form-input title="{{ __('console/shipping.priority') }}" name="priority"
            :value="old('priority', $template->priority ?? 0)" />
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection
