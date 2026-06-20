@extends('console::layouts.app')

@section('title', __('SignIn::common.menu'))

@section('content')
  <div class="row mb-3">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <div class="h3 mb-0">{{ $todayCount }}</div>
          <div class="text-muted small">{{ __('SignIn::common.today_count') }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('SignIn::common.customer_id') }}</th>
            <th>{{ __('SignIn::common.sign_date') }}</th>
            <th>{{ __('SignIn::common.points') }}</th>
            <th>{{ __('SignIn::common.continuous_days') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($logs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ $log->customer_id }}</td>
              <td>{{ $log->sign_date->toDateString() }}</td>
              <td>+{{ $log->points }}</td>
              <td>{{ $log->continuous_days }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">{{ __('SignIn::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $logs->links() }}</div>
    </div>
  </div>
@endsection
