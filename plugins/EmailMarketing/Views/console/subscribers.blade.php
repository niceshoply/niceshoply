@extends('console::layouts.app')

@section('title', __('EmailMarketing::common.menu_subscribers'))

@section('content')
  <div class="mb-3 d-flex justify-content-between">
    <span class="badge bg-success fs-6">{{ __('EmailMarketing::common.total_active') }}: {{ $total }}</span>
    <a href="{{ console_route('email_marketing.campaigns') }}" class="btn btn-outline-secondary btn-sm">{{ __('EmailMarketing::common.menu_campaigns') }}</a>
  </div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>#</th><th>{{ __('EmailMarketing::common.email') }}</th><th>{{ __('EmailMarketing::common.customer') }}</th>
        <th>{{ __('EmailMarketing::common.state') }}</th><th>{{ __('EmailMarketing::common.created_at') }}</th>
      </tr></thead>
      <tbody>
      @forelse($subscribers as $s)
        <tr>
          <td>{{ $s->id }}</td>
          <td>{{ $s->email }}</td>
          <td>{{ $s->customer_id ?: '-' }}</td>
          <td>{!! $s->subscribed ? '<span class="badge bg-success">'.__('EmailMarketing::common.on').'</span>' : '<span class="badge bg-secondary">'.__('EmailMarketing::common.off').'</span>' !!}</td>
          <td>{{ optional($s->created_at)->format('Y-m-d H:i') }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('EmailMarketing::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $subscribers->links() }}</div></div>
@endsection
