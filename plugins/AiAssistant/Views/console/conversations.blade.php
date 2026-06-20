@extends('console::layouts.app')

@section('title', __('AiAssistant::common.log_title'))

@section('content')
  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('AiAssistant::common.visitor') }}</th><th>{{ __('AiAssistant::common.question') }}</th>
        <th>{{ __('AiAssistant::common.answer') }}</th><th>{{ __('AiAssistant::common.time') }}</th>
      </tr></thead>
      <tbody>
      @forelse($logs as $l)
        <tr>
          <td class="small">{{ $l->visitor_key }}</td>
          <td>{{ $l->question }}</td>
          <td class="small text-muted">{{ \Illuminate\Support\Str::limit($l->answer, 200) }}</td>
          <td class="small">{{ $l->created_at }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('AiAssistant::common.no_log') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $logs->links() }}</div></div>
@endsection
