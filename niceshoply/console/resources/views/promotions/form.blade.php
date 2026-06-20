@extends('console::layouts.app')

@section('title', __('console/promotion.promotions'))

<x-console::form.right-btns />

@php
  // 把结构化条件/动作回填为扁平表单值
  $conditions = $promotion->conditions ?? [];
  $actions = $promotion->actions ?? [];
  $conditionValue = $conditions['min_amount'] ?? ($conditions['min_qty'] ?? '');
  $tiersText = '';
  foreach (($conditions['tiers'] ?? []) as $tier) {
      $tiersText .= ($tier['min'] ?? 0).':'.($tier['value'] ?? 0)."\n";
  }
@endphp

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $promotion->id ? console_route('promotions.update', [$promotion->id]) : console_route('promotions.store') }}" method="POST">
  @csrf
  @method($promotion->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12 col-md-9">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('console/common.basic_info') }}</h5>
        </div>
        <div class="card-body">
          <x-common-form-input title="{{ __('console/promotion.name') }}" name="name"
            :value="old('name', $promotion->name ?? '')" required placeholder="{{ __('console/promotion.name_hint') }}" />

          <x-common-form-input title="{{ __('console/promotion.label') }}" name="label"
            :value="old('label', $promotion->translation->label ?? '')" placeholder="{{ __('console/promotion.label_hint') }}" />

          <x-common-form-input title="{{ __('console/promotion.description') }}" name="description"
            :value="old('description', $promotion->translation->description ?? '')" />

          <x-common-form-select title="{{ __('console/promotion.scope') }}" name="scope"
            :options="$scopeOptions" key="code" label="label" value="{{ old('scope', $promotion->scope ?? 'cart') }}" />
        </div>
      </div>

      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('console/promotion.rule') }}</h5>
        </div>
        <div class="card-body">
          <x-common-form-select title="{{ __('console/promotion.condition_type') }}" name="condition_type"
            :options="$conditionTypeOptions" key="code" label="label" value="{{ old('condition_type', $promotion->condition_type ?? 'none') }}" />

          <x-common-form-input title="{{ __('console/promotion.condition_value') }}" name="condition_value"
            :value="old('condition_value', $conditionValue)" placeholder="{{ __('console/promotion.condition_value_hint') }}" />

          <x-common-form-textarea title="{{ __('console/promotion.tiers') }}" name="tiers"
            :value="old('tiers', $tiersText)" description="{{ __('console/promotion.tiers_hint') }}" />

          <x-common-form-select title="{{ __('console/promotion.action_type') }}" name="action_type"
            :options="$actionTypeOptions" key="code" label="label" value="{{ old('action_type', $promotion->action_type ?? 'fixed') }}" />

          <x-common-form-input title="{{ __('console/promotion.action_value') }}" name="action_value"
            :value="old('action_value', $actions['value'] ?? '')" placeholder="{{ __('console/promotion.action_value_hint') }}" />

          <x-common-form-input title="{{ __('console/promotion.action_max') }}" name="action_max"
            :value="old('action_max', $actions['max'] ?? '')" placeholder="{{ __('console/promotion.action_max_hint') }}" />
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3 ps-md-0">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active"
            :value="old('active', $promotion->active ?? true)" />

          <x-common-form-switch-radio title="{{ __('console/promotion.exclusive') }}" name="exclusive"
            :value="old('exclusive', $promotion->exclusive ?? false)" />

          <x-common-form-input title="{{ __('console/promotion.priority') }}" name="priority"
            :value="old('priority', $promotion->priority ?? 0)" />

          <x-common-form-input title="{{ __('console/promotion.usage_limit') }}" name="usage_limit"
            :value="old('usage_limit', $promotion->usage_limit ?? 0)" />

          <x-common-form-input title="{{ __('console/promotion.per_customer_limit') }}" name="per_customer_limit"
            :value="old('per_customer_limit', $promotion->per_customer_limit ?? 0)" />

          <x-common-form-date title="{{ __('console/promotion.starts_at') }}" name="starts_at"
            :value="old('starts_at', optional($promotion->starts_at)->format('Y-m-d H:i:s'))" />

          <x-common-form-date title="{{ __('console/promotion.ends_at') }}" name="ends_at"
            :value="old('ends_at', optional($promotion->ends_at)->format('Y-m-d H:i:s'))" />
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection
