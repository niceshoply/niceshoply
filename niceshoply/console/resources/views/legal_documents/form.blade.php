@extends('console::layouts.app')
@section('title', __('console/legal.title'))
<x-console::form.right-btns />
@section('content')
<form class="needs-validation" novalidate id="app-form"
  action="{{ $document->id ? console_route('legal_documents.update', [$document->id]) : console_route('legal_documents.store') }}" method="POST">
  @csrf
  @method($document->id ? 'PUT' : 'POST')
  <div class="row">
    <div class="col-md-9">
      <div class="card mb-3">
        <div class="card-body">
          <x-common-form-select title="{{ __('console/legal.type') }}" name="type" :options="$typeOptions" key="code" label="label"
            value="{{ old('type', $document->type ?? 'privacy') }}" />
          <x-common-form-input title="{{ __('console/legal.version') }}" name="version" :value="old('version', $document->version ?? '1.0')" />
          @foreach(locales() as $locale)
          @php($code = $locale->code)
          <hr>
          <h6>{{ $locale->name }}</h6>
          <x-common-form-input title="{{ __('console/legal.doc_title') }}" name="translations[{{ $code }}][title]"
            :value="old('translations.'.$code.'.title', $document->id ? $document->translate($code, 'title') : '')" />
          <x-common-form-textarea title="{{ __('console/legal.content') }}" name="translations[{{ $code }}][content]"
            :value="old('translations.'.$code.'.content', $document->id ? $document->translate($code, 'content') : '')" />
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active" :value="old('active', $document->active ?? true)" />
          <x-common-form-switch-radio title="{{ __('console/legal.require_reconsent') }}" name="require_reconsent" :value="old('require_reconsent', $document->require_reconsent ?? true)" />
        </div>
      </div>
    </div>
  </div>
  <button type="submit" class="d-none"></button>
</form>
@endsection
