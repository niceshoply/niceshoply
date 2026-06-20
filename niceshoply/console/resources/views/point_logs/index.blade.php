@extends('console::layouts.app')
@section('body-class', 'page-point-log')

@section('title', __('console/point.point_logs'))

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('point_logs.index')" />

    <div class="card mb-3">
      <div class="card-header">{{ __('console/point.adjust_points') }}</div>
      <div class="card-body">
        <form action="{{ console_route('point_logs.adjust') }}" method="POST" class="row g-3">
          @csrf
          <div class="col-md-3">
            <label class="form-label">{{ __('console/point.customer_id') }}</label>
            <input type="number" name="customer_id" class="form-control" required min="1" value="{{ old('customer_id') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('console/point.points') }}</label>
            <input type="number" name="points" class="form-control" required value="{{ old('points') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('console/point.comment') }}</label>
            <input type="text" name="comment" class="form-control" value="{{ old('comment') }}">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">{{ __('console/point.adjust_submit') }}</button>
          </div>
        </form>
      </div>
    </div>

    @if ($logs->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/point.customer_id') }}</td>
            <td>{{ __('console/point.type') }}</td>
            <td>{{ __('console/point.points') }}</td>
            <td>{{ __('console/point.source') }}</td>
            <td>{{ __('console/point.reference_id') }}</td>
            <td>{{ __('console/point.comment') }}</td>
            <td>{{ __('console/common.created_at') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($logs as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>
              @if($item->customer)
              <a href="{{ console_route('customers.edit', [$item->customer_id]) }}" class="text-decoration-none">{{ $item->customer->name }}</a>
              @else
              {{ $item->customer_id }}
              @endif
            </td>
            <td>{{ __('console/point.type_'.$item->type) }}</td>
            <td>{{ $item->points }}</td>
            <td>{{ $item->source }}</td>
            <td>{{ $item->reference_id ?: '-' }}</td>
            <td>{{ $item->comment ?: '-' }}</td>
            <td>{{ $item->created_at }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $logs->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
