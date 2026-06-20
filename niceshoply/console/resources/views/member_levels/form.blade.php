@extends('console::layouts.app')

@section('title', __('console/member.member_levels'))

<x-console::form.right-btns />

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $memberLevel->id ? console_route('member_levels.update', [$memberLevel->id]) : console_route('member_levels.store') }}" method="POST">
  @csrf
  @method($memberLevel->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12 col-md-9">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('console/common.basic_info') }}</h5>
        </div>
        <div class="card-body">
          <x-common-form-input title="{{ __('console/member.name') }}" name="name"
            :value="old('name', $memberLevel->name ?? '')" required />

          <x-common-form-input title="{{ __('console/member.label') }}" name="label"
            :value="old('label', $memberLevel->translation->label ?? '')" />

          <x-common-form-input title="{{ __('console/member.description') }}" name="description"
            :value="old('description', $memberLevel->translation->description ?? '')" />

          <x-common-form-select title="{{ __('console/member.threshold_type') }}" name="threshold_type"
            :options="$thresholdTypeOptions" key="code" label="label"
            value="{{ old('threshold_type', $memberLevel->threshold_type ?? 'amount') }}" />

          <x-common-form-input title="{{ __('console/member.threshold_value') }}" name="threshold_value"
            :value="old('threshold_value', $memberLevel->threshold_value ?? 0)" />

          <x-common-form-input title="{{ __('console/member.discount_percent') }}" name="discount_percent"
            :value="old('discount_percent', $memberLevel->discount_percent ?? 0)" />
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3 ps-md-0">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active"
            :value="old('active', $memberLevel->active ?? true)" />

          <x-common-form-switch-radio title="{{ __('console/member.free_shipping') }}" name="free_shipping"
            :value="old('free_shipping', $memberLevel->free_shipping ?? false)" />

          <x-common-form-input title="{{ __('console/member.priority') }}" name="priority"
            :value="old('priority', $memberLevel->priority ?? 0)" />
        </div>
      </div>
    </div>
  </div>
</form>
@endsection
