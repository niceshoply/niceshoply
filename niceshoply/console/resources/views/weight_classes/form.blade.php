@extends('console::layouts.app')

@section('title', __('console/menu.weight_classes'))

<x-console::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ $weight_class->id ? __('console/weight_class.edit') : __('console/weight_class.create') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form"
      action="{{ $weight_class->id ? console_route('weight_classes.update', $weight_class->id) : console_route('weight_classes.store') }}"
      method="POST">
      @csrf
      @method($weight_class->id ? 'PUT' : 'POST')

      <div class="row">
        <div class="col-md-6">
          <x-common-form-input
              :title="__('console/weight_class.name')"
              name="name"
              :value="old('name', $weight_class->name)"
              :placeholder="__('console/weight_class.name')"
              required
          />
        </div>
        <div class="col-md-6">
          <x-common-form-input
              :title="__('console/weight_class.code')"
              name="code"
              :value="old('code', $weight_class->code)"
              :placeholder="__('console/weight_class.code')"
              :readonly="$weight_class->id ? true : false"
              required
          />
          <small class="form-text text-muted">{{ __('console/weight_class.code_help') }}</small>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6">
          <x-common-form-input
              :title="__('console/weight_class.unit')"
              name="unit"
              :value="old('unit', $weight_class->unit)"
              :placeholder="__('console/weight_class.unit')"
              required
          />
          <small class="form-text text-muted">{{ __('console/weight_class.unit_help') }}</small>
        </div>
        <div class="col-md-6">
          <x-common-form-input
              :title="__('console/weight_class.value')"
              name="value"
              type="number"
              step="0.000001"
              min="0.000001"
              :value="old('value', $weight_class->value)"
              :placeholder="__('console/weight_class.value')"
              required
          />
          <small class="form-text text-muted">{{ __('console/weight_class.value_help') }}</small>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6">
          <x-common-form-input
              :title="__('console/weight_class.position')"
              name="position"
              type="number"
              min="0"
              :value="old('position', $weight_class->position ?? 0)"
              :placeholder="__('console/weight_class.position')"
          />
        </div>
        <div class="col-md-6">
          <x-common-form-switch-radio 
              :title="__('console/weight_class.active')"
              name="active"
              :value="old('active', $weight_class->active ?? true)"
          />
        </div>
      </div>
      
      <div class="mt-4">
        <h5>{{ __('console/weight_class.info') }}</h5>
        <p>{{ __('console/weight_class.description') }}</p>
        <ul>
          <li>{{ __('console/weight_class.info_1') }}</li>
          <li>{{ __('console/weight_class.info_2') }}</li>
          <li>{{ __('console/weight_class.info_3') }}</li>
        </ul>
      </div>

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
@endsection 