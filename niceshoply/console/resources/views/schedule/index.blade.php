@extends('console::layouts.app')
@section('body-class', 'page-schedule')
@section('title', __('console/schedule.title'))
@section('page-subtitle', __('console/schedule.subtitle'))

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <div class="alert alert-info small mb-3">
      <i class="bi bi-info-circle me-1"></i>{{ __('console/schedule.cron_hint') }}
    </div>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/schedule.command') }}</td>
            <td>{{ __('console/schedule.expression') }}</td>
            <td>{{ __('console/schedule.description') }}</td>
            <td>{{ __('console/schedule.last_run') }}</td>
            <td></td>
          </tr>
        </thead>
        <tbody>
          @foreach($tasks as $task)
          <tr>
            <td><code>{{ $task['command'] }}</code></td>
            <td><code>{{ $task['expression'] }}</code></td>
            <td>{{ $task['description'] }}</td>
            <td>
              @if($task['last_run'])
              <span class="badge bg-{{ $task['last_run']->status === 'failed' ? 'danger' : 'success' }}">
                {{ __('console/schedule.status_'.$task['last_run']->status) }}
              </span>
              <small class="text-muted d-block">{{ $task['last_run']->ran_at }}</small>
              @else
              <span class="text-muted">{{ __('console/schedule.never') }}</span>
              @endif
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-outline-primary btn-run" data-command="{{ $task['command'] }}">
                {{ __('console/schedule.run_now') }}
              </button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const url = '{{ console_route('schedule.run') }}';
  const confirmMsg = @json(__('console/schedule.confirm_run'));

  function notify(msg) {
    if (window.inno && typeof inno.msg === 'function') inno.msg(msg);
    else alert(msg);
  }

  document.querySelectorAll('.btn-run').forEach(btn => {
    btn.addEventListener('click', function () {
      const command = this.dataset.command;
      if (!confirm(confirmMsg.replace(':command', command))) return;
      this.disabled = true;
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ command }),
      }).then(r => r.json()).then(res => {
        notify(res.message || res.data?.output || 'done');
        if (res.success) setTimeout(() => window.location.reload(), 1500);
        else this.disabled = false;
      }).catch(() => { this.disabled = false; });
    });
  });
});
</script>
@endpush
