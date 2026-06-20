@extends('console::layouts.app')

@section('title', __('LuckyDraw::common.records_title'))

@section('content')
  <div class="mb-3"><a href="{{ console_route('lucky_draw.prizes') }}" class="btn btn-sm btn-outline-secondary">← {{ __('LuckyDraw::common.prizes_title') }}</a></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('LuckyDraw::common.customer') }}</th><th>{{ __('LuckyDraw::common.prize') }}</th>
        <th>{{ __('LuckyDraw::common.type') }}</th><th>{{ __('LuckyDraw::common.result') }}</th><th>{{ __('LuckyDraw::common.time') }}</th>
      </tr></thead>
      <tbody>
      @forelse($records as $r)
        <tr>
          <td>{{ $r->customer_id }}</td>
          <td>{{ $r->prize_name }}</td>
          <td>{{ $r->prize_type }}</td>
          <td class="small">{{ $r->result_value ?: '-' }}</td>
          <td class="small">{{ $r->created_at }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('LuckyDraw::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $records->links() }}</div></div>
@endsection
