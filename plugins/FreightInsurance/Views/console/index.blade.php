@extends('console::layouts.app')

@section('title', __('FreightInsurance::common.menu'))

@section('content')
  <div class="mb-3">
    <span class="badge bg-primary fs-6">{{ __('FreightInsurance::common.total') }}: {{ currency_format($total) }}</span>
  </div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('FreightInsurance::common.id') }}</th>
        <th>{{ __('FreightInsurance::common.order') }}</th>
        <th>{{ __('FreightInsurance::common.customer') }}</th>
        <th>{{ __('FreightInsurance::common.premium') }}</th>
        <th>{{ __('FreightInsurance::common.status') }}</th>
        <th>{{ __('FreightInsurance::common.created_at') }}</th>
      </tr></thead>
      <tbody>
      @forelse($records as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->order_number }}</td>
          <td>{{ $r->customer_id ?: '-' }}</td>
          <td>{{ currency_format($r->premium) }}</td>
          <td><span class="badge bg-success">{{ __('FreightInsurance::common.status_'.$r->status) }}</span></td>
          <td>{{ optional($r->created_at)->format('Y-m-d H:i') }}</td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('FreightInsurance::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $records->links() }}</div></div>
@endsection
