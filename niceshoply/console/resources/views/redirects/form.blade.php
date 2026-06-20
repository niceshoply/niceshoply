@extends('console::layouts.app')

@section('title', __('console/redirect.title'))

<x-console::form.right-btns />

@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $redirect->id ? console_route('redirects.update', [$redirect->id]) : console_route('redirects.store') }}" method="POST">
  @csrf
  @method($redirect->id ? 'PUT' : 'POST')

  <div class="row">
    <div class="col-12 col-md-9">
      <div class="card mb-3">
        <div class="card-header">
          <h5 class="card-title mb-0">{{ __('console/common.basic_info') }}</h5>
        </div>
        <div class="card-body">
          <x-common-form-input title="{{ __('console/redirect.source_path') }}" name="source_path"
            :value="old('source_path', $redirect->source_path ?? '')" required
            description="{{ __('console/redirect.source_path_hint') }}" />

          <x-common-form-input title="{{ __('console/redirect.target_path') }}" name="target_path"
            :value="old('target_path', $redirect->target_path ?? '')" required
            description="{{ __('console/redirect.target_path_hint') }}" />

          <x-common-form-select title="{{ __('console/redirect.status_code') }}" name="status_code"
            :options="[
              ['code' => '301', 'label' => '301 ' . __('console/redirect.permanent')],
              ['code' => '302', 'label' => '302 ' . __('console/redirect.temporary')],
            ]" key="code" label="label"
            value="{{ old('status_code', $redirect->status_code ?? 301) }}" />
        </div>
      </div>
    </div>

    <div class="col-12 col-md-3 ps-md-0">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active"
            :value="old('active', $redirect->active ?? true)" />
          @if($redirect->id)
          <div class="mt-3 text-muted small">
            {{ __('console/redirect.hits') }}: <strong>{{ $redirect->hits }}</strong>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection
