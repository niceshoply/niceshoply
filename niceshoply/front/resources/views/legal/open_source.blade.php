@extends('layouts.app')
@section('body-class', 'page-legal page-license')

@section('title', __('front/license.open_source_title'))

@section('content')
<div class="container py-4">
  <div class="card mb-4">
    <div class="card-body">
      <h1 class="h3 mb-3">{{ __('front/license.open_source_title') }}</h1>
      <p class="text-muted small mb-4">{{ __('front/license.last_updated') }}: {{ $updatedAt }}</p>

      <div class="alert alert-success">
        <h2 class="h5 mb-2">{{ __('front/license.source_code_heading') }}</h2>
        <p class="mb-0">{{ __('front/license.source_code_intro') }}</p>
      </div>

      <h2 class="h5 mt-4">{{ __('front/license.shopify_compare_heading') }}</h2>
      <div class="table-responsive">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th></th>
              <th>Shopify</th>
              <th>NiceShoply</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ __('front/license.open_source_title') }}</td>
              <td>{{ __('front/common.no', ['default' => 'No']) }}</td>
              <td>MIT</td>
            </tr>
            <tr>
              <td>Source code</td>
              <td>Closed</td>
              <td>{{ __('common/powered_by.source_code_promise') }}</td>
            </tr>
            <tr>
              <td>Self-host</td>
              <td>{{ __('front/common.no', ['default' => 'No']) }}</td>
              <td>{{ __('front/common.yes', ['default' => 'Yes']) }}</td>
            </tr>
            <tr>
              <td>Platform GMV fee</td>
              <td>{{ __('front/common.yes', ['default' => 'Yes']) }}</td>
              <td>{{ __('front/common.no', ['default' => 'No']) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h2 class="h5 mt-4">{{ __('front/license.mit_heading') }}</h2>
      <pre class="bg-light p-3 rounded small" style="white-space: pre-wrap;">{{ $licenseText }}</pre>

      <p class="mt-3 mb-0">
        <a href="{{ front_route('legal.commercial') }}">{{ __('front/license.view_commercial') }}</a>
      </p>
    </div>
  </div>
</div>
@endsection
