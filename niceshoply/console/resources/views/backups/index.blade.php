@extends('console::layouts.app')
@section('body-class', 'page-backups')
@section('title', __('console/backup.title'))
@section('page-subtitle', __('console/backup.subtitle'))

@section('page-title-right')
  <button type="button" class="btn btn-primary" id="btn-backup" @disabled($is_running)>
    <i class="bi bi-cloud-download"></i> {{ __('console/backup.create') }}
  </button>
@endsection

@section('content')
<div class="row g-3">
  <div class="col-12 {{ $is_running ? '' : 'd-none' }}" id="progress-card">
    <div class="card">
      <div class="card-body">
        <div class="alert alert-warning small">
          <i class="bi bi-exclamation-circle me-1"></i>{{ __('console/backup.do_not_close') }}
        </div>
        <div class="d-flex justify-content-between mb-1">
          <span class="fw-semibold" id="progress-message">{{ $progress['message'] ?? '' }}</span>
          <span class="badge bg-secondary" id="progress-status"></span>
        </div>
        <div class="progress mb-2" style="height: 22px;">
          <div class="progress-bar progress-bar-striped progress-bar-animated" id="progress-bar"
               style="width: {{ (int) ($progress['percent'] ?? 0) }}%;">
            {{ (int) ($progress['percent'] ?? 0) }}%
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card h-min-600">
      <div class="card-body">
        @if ($backups->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <td>{{ __('console/common.id') }}</td>
                <td>{{ __('console/backup.status') }}</td>
                <td>{{ __('console/backup.file_size') }}</td>
                <td>{{ __('console/backup.triggered_by') }}</td>
                <td>{{ __('console/common.created_at') }}</td>
                <td></td>
              </tr>
            </thead>
            <tbody>
              @foreach($backups as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <span class="badge bg-{{ $item->status === 'completed' ? 'success' : ($item->status === 'failed' ? 'danger' : 'secondary') }}">
                    {{ __('console/backup.status_'.$item->status) }}
                  </span>
                </td>
                <td>{{ $item->file_size ? number_format($item->file_size / 1024, 1).' KB' : '-' }}</td>
                <td>{{ __('console/backup.trigger_'.$item->triggered_by) }}</td>
                <td>{{ $item->created_at }}</td>
                <td class="text-end">
                  @if($item->status === 'completed')
                  <a href="{{ console_route('backups.download', [$item->id]) }}" class="btn btn-sm btn-outline-primary">
                    {{ __('console/backup.download') }}
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-danger btn-restore" data-id="{{ $item->id }}">
                    {{ __('console/backup.restore') }}
                  </button>
                  @elseif($item->status === 'failed')
                  <small class="text-danger">{{ Str::limit($item->error_message, 60) }}</small>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        {{ $backups->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
        @else
        <x-common-no-data />
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const urls = {
    store: '{{ console_route('backups.store') }}',
    progress: '{{ console_route('backups.progress') }}',
    restore: '{{ console_route('backups.restore', ['id' => '__ID__']) }}',
  };
  const statusText = {
    queued: '{{ __('console/backup.status_queued') }}',
    running: '{{ __('console/backup.status_running') }}',
    success: '{{ __('console/backup.status_success') }}',
    failed: '{{ __('console/backup.status_failed') }}',
  };
  const lang = {
    confirm: '{{ __('console/backup.confirm_create') }}',
    restoreConfirm: '{{ __('console/backup.confirm_restore') }}',
  };

  const btnBackup = document.getElementById('btn-backup');
  const progressCard = document.getElementById('progress-card');
  const progressBar = document.getElementById('progress-bar');
  const progressMessage = document.getElementById('progress-message');
  const progressStatus = document.getElementById('progress-status');
  let pollTimer = null;

  function notify(msg) {
    if (window.inno && typeof inno.msg === 'function') inno.msg(msg);
    else alert(msg);
  }

  function renderProgress(p) {
    const percent = parseInt(p.percent || 0, 10);
    progressBar.style.width = percent + '%';
    progressBar.textContent = percent + '%';
    progressMessage.textContent = p.message || '';
    const status = p.status || 'idle';
    progressStatus.textContent = statusText[status] || status;
    if (status === 'success' || status === 'failed') {
      progressBar.classList.remove('progress-bar-animated');
      stopPolling();
      if (status === 'success') setTimeout(() => window.location.reload(), 2000);
    }
  }

  function poll() {
    fetch(urls.progress, { headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(res => renderProgress(res.data || {}))
      .catch(() => {});
  }

  function startPolling() {
    if (pollTimer) return;
    progressCard.classList.remove('d-none');
    poll();
    pollTimer = setInterval(poll, 2500);
  }

  function stopPolling() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    if (btnBackup) btnBackup.disabled = false;
  }

  if (btnBackup) {
    btnBackup.addEventListener('click', function () {
      if (!confirm(lang.confirm)) return;
      btnBackup.disabled = true;
      fetch(urls.store, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      }).then(r => r.json()).then(res => {
        if (!res.success) { notify(res.message); btnBackup.disabled = false; return; }
        notify(res.message);
        startPolling();
      }).catch(e => { notify(String(e)); btnBackup.disabled = false; });
    });
  }

  document.querySelectorAll('.btn-restore').forEach(btn => {
    btn.addEventListener('click', function () {
      if (!confirm(lang.restoreConfirm)) return;
      const id = this.dataset.id;
      const url = urls.restore.replace('__ID__', id);
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      }).then(r => r.json()).then(res => {
        notify(res.message || (res.success ? 'OK' : 'Error'));
        if (res.success) setTimeout(() => window.location.reload(), 2000);
      });
    });
  });

  @if($is_running)
    startPolling();
  @endif
});
</script>
@endpush
