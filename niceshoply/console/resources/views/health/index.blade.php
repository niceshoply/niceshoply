@extends('console::layouts.app')
@section('body-class', 'page-health')
@section('title', __('console/health.title'))
@section('page-subtitle', __('console/health.subtitle'))

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <div class="alert alert-{{ $isHealthy ? 'success' : 'warning' }} d-flex align-items-center mb-4">
      <i class="bi bi-{{ $isHealthy ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
      <div>{{ $isHealthy ? __('console/health.all_ok') : __('console/health.has_issues') }}</div>
    </div>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/health.item') }}</td>
            <td>{{ __('console/health.result') }}</td>
            <td>{{ __('console/health.detail') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($checks as $key => $check)
          <tr>
            <td>{{ $check['label'] ?? $key }}</td>
            <td>
              <span class="badge bg-{{ ($check['ok'] ?? false) ? 'success' : 'danger' }}">
                {{ ($check['ok'] ?? false) ? __('console/health.pass') : __('console/health.fail') }}
              </span>
              <span class="ms-2">{{ $check['message'] ?? '' }}</span>
            </td>
            <td class="text-muted small">{{ $check['detail'] ?? '' }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
