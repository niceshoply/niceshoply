@extends('console::layouts.app')

@section('title', __('console/menu.brands'))

<x-console::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/menu.brands') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form"
      action="{{ $brand->id ? console_route('brands.update', [$brand->id]) : console_route('brands.store') }}"
      method="POST">
      @csrf
      @method($brand->id ? 'PUT' : 'POST')

      <x-common-form-input title="{{ __('console/brand.name') }}" name="name" value="{{ old('name', $brand->name) }}" required placeholder="{{ __('console/brand.name') }}" />
      <x-common-form-image title="{{ __('console/brand.logo') }}" name="logo" value="{{ old('logo', $brand->logo) }}" required />
      <x-common-form-input title="{{ __('console/brand.first') }}" name="first" value="{{ old('first', $brand->first) }}" required placeholder="{{ __('console/brand.first') }}" />
      <x-common-form-input title="{{ __('console/common.position') }}" name="position" value="{{ old('position', $brand->position) }}" placeholder="{{ __('console/common.position') }}" />
      <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active" :value="old('active', $page->active ?? true)" placeholder="{{ __('console/common.whether_enable') }}"/>
      <x-common-form-input title="{{ __('console/common.slug') }}" name="slug" value="{{ old('slug', $brand->slug) }}" placeholder="{{ __('console/common.slug') }}" />

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script></script>
@endpush