@extends('console::layouts.app')

@section('title', __('RiskControl::common.ev_title'))

@section('content')
  <form class="row g-2 mb-3" method="get">
    <div class="col-auto">
      <select name="level" class="form-select" onchange="this.form.submit()">
        <option value="">{{ __('RiskControl::common.level') }}: {{ __('RiskControl::common.all') }}</option>
        @foreach(['low','medium','high'] as $lv)
          <option value="{{ $lv }}" @selected(request('level')===$lv)>{{ __('RiskControl::common.level_'.$lv) }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <select name="scene" class="form-select" onchange="this.form.submit()">
        <option value="">{{ __('RiskControl::common.scene') }}: {{ __('RiskControl::common.all') }}</option>
        @foreach(['register','order'] as $sc)
          <option value="{{ $sc }}" @selected(request('scene')===$sc)>{{ __('RiskControl::common.scene_'.$sc) }}</option>
        @endforeach
      </select>
    </div>
  </form>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('RiskControl::common.level') }}</th><th>{{ __('RiskControl::common.scene') }}</th>
        <th>{{ __('RiskControl::common.rule') }}</th><th>{{ __('RiskControl::common.ip') }}</th>
        <th>{{ __('RiskControl::common.subject') }}</th><th>{{ __('RiskControl::common.detail') }}</th>
        <th>{{ __('RiskControl::common.created_at') }}</th>
      </tr></thead>
      <tbody>
      @forelse($events as $e)
        <tr>
          <td>
            @php $cls = ['low'=>'secondary','medium'=>'warning','high'=>'danger'][$e->level] ?? 'secondary'; @endphp
            <span class="badge bg-{{ $cls }}">{{ __('RiskControl::common.level_'.$e->level) }}</span>
          </td>
          <td>{{ __('RiskControl::common.scene_'.$e->scene) }}</td>
          <td><code>{{ $e->rule }}</code></td>
          <td>{{ $e->ip }}</td>
          <td>{{ $e->subject }}</td>
          <td class="small text-muted">{{ $e->detail }}</td>
          <td>{{ optional($e->created_at)->format('Y-m-d H:i') }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('RiskControl::common.no_events') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $events->links() }}</div></div>
@endsection
