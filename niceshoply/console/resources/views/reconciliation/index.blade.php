@extends('console::layouts.app')
@section('body-class', 'page-reconciliation')

@section('title', __('console/reconciliation.title'))

@section('page-title-right')
<a href="{{ console_route('reconciliation.export', ['start' => $start, 'end' => $end]) }}" class="btn btn-outline-primary">
  <i class="bi bi-download me-1"></i>{{ __('console/reconciliation.export') }}
</a>
@endsection

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <form class="row g-3 mb-4" method="GET" action="{{ console_route('reconciliation.index') }}">
      <div class="col-md-4">
        <label class="form-label">{{ __('console/reconciliation.start_date') }}</label>
        <input type="date" name="start" class="form-control" value="{{ $start }}">
      </div>
      <div class="col-md-4">
        <label class="form-label">{{ __('console/reconciliation.end_date') }}</label>
        <input type="date" name="end" class="form-control" value="{{ $end }}">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-primary">{{ __('console/common.search') }}</button>
      </div>
    </form>

    <div class="row mb-4">
      <div class="col-md-3">
        <div class="border rounded p-3">
          <div class="text-muted small">{{ __('console/reconciliation.income') }}</div>
          <div class="fs-4 fw-bold text-success">{{ currency_format($summary['income']) }}</div>
          <div class="text-muted small">{{ __('console/reconciliation.order_count', ['count' => $summary['order_count']]) }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="border rounded p-3">
          <div class="text-muted small">{{ __('console/reconciliation.refunds') }}</div>
          <div class="fs-4 fw-bold text-danger">{{ currency_format($summary['refunds']) }}</div>
          <div class="text-muted small">{{ __('console/reconciliation.refund_count', ['count' => $summary['refund_count']]) }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="border rounded p-3">
          <div class="text-muted small">{{ __('console/reconciliation.fees') }}</div>
          <div class="fs-4 fw-bold text-warning">{{ currency_format($summary['fees']) }}</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="border rounded p-3">
          <div class="text-muted small">{{ __('console/reconciliation.net') }}</div>
          <div class="fs-4 fw-bold text-primary">{{ currency_format($summary['net']) }}</div>
        </div>
      </div>
    </div>

    @if (count($breakdown))
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/reconciliation.date') }}</td>
            <td>{{ __('console/reconciliation.income') }}</td>
            <td>{{ __('console/reconciliation.refunds') }}</td>
            <td>{{ __('console/reconciliation.fees') }}</td>
            <td>{{ __('console/reconciliation.net') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($breakdown as $row)
          <tr>
            <td>{{ $row['date'] }}</td>
            <td>{{ currency_format($row['income']) }}</td>
            <td class="text-danger">{{ currency_format($row['refunds']) }}</td>
            <td>{{ currency_format($row['fees']) }}</td>
            <td class="fw-bold">{{ currency_format($row['net']) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
