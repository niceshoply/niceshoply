@extends('console::layouts.app')

@section('title', __('Distribution::common.distributors'))

@section('page-title-right')
  <a href="{{ console_route('distribution.commissions') }}" class="btn btn-light btn-sm">{{ __('Distribution::common.commissions') }}</a>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Distribution::common.distributor') }}</th>
            <th>{{ __('Distribution::common.code') }}</th>
            <th>{{ __('Distribution::common.total_commission') }}</th>
            <th>{{ __('Distribution::common.settled') }}</th>
            <th>{{ __('Distribution::common.active') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($distributors as $d)
            <tr>
              <td>{{ $d->id }}</td>
              <td>{{ $d->customer_id }}</td>
              <td><code>{{ $d->code }}</code></td>
              <td>{{ currency_format($d->total_commission) }}</td>
              <td>{{ currency_format($d->settled_commission) }}</td>
              <td>
                @if($d->active)<span class="badge bg-success">{{ __('Distribution::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('Distribution::common.no') }}</span>@endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('Distribution::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $distributors->links() }}
    </div>
  </div>
@endsection
