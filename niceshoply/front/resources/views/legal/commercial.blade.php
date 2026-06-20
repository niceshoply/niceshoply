@extends('layouts.app')
@section('body-class', 'page-legal page-license')

@section('title', __('front/license.commercial_title'))

@section('content')
<div class="container py-4">
  <div class="card">
    <div class="card-body">
      <h1 class="h3 mb-3">{{ __('front/license.commercial_title') }}</h1>
      <p class="text-muted small mb-4">{{ __('front/license.last_updated') }}: {{ $updatedAt }}</p>

      <p>{{ __('front/license.source_code_intro') }}</p>

      <h2 class="h5 mt-4">{{ __('front/license.commercial_title') }}</h2>
      <p>MIT {{ __('front/license.open_source_title') }} {{ __('common/powered_by.source_code_promise') }}</p>

      <div class="table-responsive">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>{{ __('front/common.action') }}</th>
              <th>{{ __('front/license.commercial_title') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ __('front/common.default') }}</td>
              <td>{!! __('common/powered_by.front_link') !!}</td>
            </tr>
            <tr>
              <td>{{ __('front/license.view_commercial') }}</td>
              <td>{{ __('front/license.commercial_title') }} — Powered by {{ __('front/common.no', ['default' => 'hidden']) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <h3 class="h6 mt-4">Branding Removal / White-Label</h3>
      <ul>
        <li><strong>Branding Removal</strong> — Remove default Powered by link on front and console.</li>
        <li><strong>Agency / White-Label</strong> — Deliver under your brand to multiple clients.</li>
        <li><strong>Enterprise</strong> — Custom terms, SLA, and dedicated support.</li>
      </ul>

      <p class="mb-0">
        <a href="{{ $marketplaceUrl }}" class="btn btn-primary" target="_blank" rel="noopener">
          {{ __('console/plugin.marketplace_settings', ['default' => 'Marketplace']) }}
        </a>
        <a href="{{ front_route('legal.open_source') }}" class="btn btn-outline-secondary ms-2">
          {{ __('front/license.view_open_source') }}
        </a>
      </p>
    </div>
  </div>
</div>
@endsection
