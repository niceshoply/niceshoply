@extends('console::layouts.app')

@section('title', __('console/return_reason.return_reasons'))

<x-console::form.right-btns />

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $reason->id ? console_route('return_reasons.update', [$reason->id]) : console_route('return_reasons.store') }}" method="POST">
  @csrf
  @method($reason->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12 col-md-9">
      <div class="card mb-3 h-min-400">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('console/common.basic_info') }}</h5>
        </div>
        <div class="card-body">
          <x-common-form-input title="{{ __('console/return_reason.name') }}" name="name"
            :value="old('name', $reason->name ?? '')" required placeholder="{{ __('console/return_reason.name') }}" />
          <x-common-form-input title="{{ __('console/return_reason.description') }}" name="description"
            :value="old('description', $reason->description ?? '')" placeholder="{{ __('console/return_reason.description_hint') }}" />
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3 ps-md-0">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active" :value="old('active', $reason->active ?? true)"
            placeholder="{{ __('console/common.whether_enable') }}" />
          <x-common-form-input title="{{ __('console/return_reason.sort_order') }}" name="sort_order" :value="old('sort_order', $reason->sort_order ?? 0)" />
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection

@push('footer')
<script>
</script>
@endpush
