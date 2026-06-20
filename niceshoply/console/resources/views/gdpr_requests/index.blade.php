@extends('console::layouts.app')
@section('body-class', 'page-gdpr-request')
@section('title', __('console/gdpr.title'))
@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('gdpr_requests.index')" />
    @if ($requests->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/gdpr.customer') }}</td>
            <td>{{ __('console/gdpr.type') }}</td>
            <td>{{ __('console/gdpr.status') }}</td>
            <td>{{ __('console/common.created_at') }}</td>
            <td></td>
          </tr>
        </thead>
        <tbody>
          @foreach($requests as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->customer->email ?? '-' }}</td>
            <td>{{ __('console/gdpr.type_'.$item->type) }}</td>
            <td>{{ __('console/gdpr.status_'.$item->status) }}</td>
            <td>{{ $item->created_at }}</td>
            <td>
              @if($item->type === 'export' && $item->status === 'completed')
              <a href="{{ console_route('gdpr_requests.download', [$item->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('console/gdpr.download') }}</a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $requests->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
