@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/system_update.title'))
@section('page-subtitle', __('console/system_update.subtitle'))

@section('page-title-right')
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-primary" id="btn-check" @disabled(!$enabled || !$has_domain_token)>
      <i class="bi bi-arrow-repeat"></i> {{ __('console/system_update.check_update') }}
    </button>
  </div>
@endsection

@section('content')
  <div class="row g-3">
    {{-- 左侧：版本信息 --}}
    <div class="col-12 col-lg-5">
      <div class="card h-100">
        <div class="card-body">
          @unless($enabled)
            <div class="alert alert-secondary d-flex align-items-center" role="alert">
              <i class="bi bi-slash-circle me-2"></i>
              <div>{{ __('console/system_update.disabled') }}</div>
            </div>
          @endunless

          @unless($has_domain_token)
            <div class="alert alert-warning d-flex align-items-center" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i>
              <div>{{ __('console/system_update.no_domain_token') }}</div>
            </div>
          @endunless

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">{{ __('console/system_update.current_version') }}</span>
              <span class="fw-semibold">{{ $current_version }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">{{ __('console/system_update.build') }}</span>
              <span>{{ $current_build ?: '-' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">{{ __('console/system_update.edition') }}</span>
              <span class="text-capitalize">{{ $current_edition }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">{{ __('console/system_update.last_upgrade') }}</span>
              <span>
                @if($last_upgrade_version)
                  {{ $last_upgrade_version }}
                  <small class="text-muted d-block text-end">{{ $last_upgrade_at }}</small>
                @else
                  <span class="text-muted">{{ __('console/system_update.last_upgrade_none') }}</span>
                @endif
              </span>
            </li>
          </ul>

          <div class="alert alert-info small mb-0">
            <i class="bi bi-info-circle me-1"></i>{{ __('console/system_update.backup_warning') }}
          </div>
        </div>
      </div>
    </div>

    {{-- 右侧：检查结果 / 升级进度 --}}
    <div class="col-12 col-lg-7">
      {{-- 检查结果区 --}}
      <div class="card mb-3 d-none" id="result-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="mb-0" id="result-title"></h5>
            <button type="button" class="btn btn-primary d-none" id="btn-start">
              <i class="bi bi-download"></i> {{ __('console/system_update.start_update') }}
            </button>
          </div>
          <div class="text-muted small mb-2" id="result-meta"></div>
          <div id="result-changelog" class="upgrade-changelog small"></div>
        </div>
      </div>

      {{-- 升级进度区 --}}
      <div class="card {{ $is_running ? '' : 'd-none' }}" id="progress-card">
        <div class="card-body">
          <div class="alert alert-warning small">
            <i class="bi bi-exclamation-circle me-1"></i>{{ __('console/system_update.do_not_close') }}
          </div>

          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="fw-semibold" id="progress-message">{{ $progress['message'] ?? '' }}</span>
            <span class="badge bg-secondary" id="progress-status"></span>
          </div>

          <div class="progress mb-3" style="height: 22px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated"
                 id="progress-bar"
                 role="progressbar"
                 style="width: {{ (int) ($progress['percent'] ?? 0) }}%;">
              {{ (int) ($progress['percent'] ?? 0) }}%
            </div>
          </div>

          <details id="log-wrapper">
            <summary class="text-muted small mb-2" style="cursor:pointer;">{{ __('console/system_update.view_logs') }}</summary>
            <pre class="upgrade-log bg-dark text-light p-2 rounded small mb-0"
                 id="progress-log"
                 style="max-height: 280px; overflow:auto; white-space: pre-wrap;"></pre>
          </details>
        </div>
      </div>

      {{-- 占位：未检查且未升级时 --}}
      <div class="card {{ $is_running ? 'd-none' : '' }}" id="empty-card">
        <div class="card-body text-center text-muted py-5">
          <i class="bi bi-cloud-arrow-down" style="font-size: 2.5rem;"></i>
          <p class="mt-2 mb-0">{{ __('console/system_update.subtitle') }}</p>
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
        check: '{{ console_route('system_update.check') }}',
        start: '{{ console_route('system_update.start') }}',
        progress: '{{ console_route('system_update.progress') }}',
      };

      const statusText = {
        idle: '{{ __('console/system_update.status_idle') }}',
        queued: '{{ __('console/system_update.status_queued') }}',
        running: '{{ __('console/system_update.status_running') }}',
        success: '{{ __('console/system_update.status_success') }}',
        failed: '{{ __('console/system_update.status_failed') }}',
      };

      const lang = {
        checking: '{{ __('console/system_update.checking') }}',
        check_update: '<i class="bi bi-arrow-repeat"></i> {{ __('console/system_update.check_update') }}',
        up_to_date: '{{ __('console/system_update.up_to_date') }}',
        confirm: @json(__('console/system_update.confirm_update', ['version' => ':version'])),
        no_update: '{{ __('console/system_update.no_update') }}',
        released_at: '{{ __('console/system_update.released_at') }}',
        package_size: '{{ __('console/system_update.package_size') }}',
        changelog: '{{ __('console/system_update.changelog') }}',
      };

      const btnCheck = document.getElementById('btn-check');
      const btnStart = document.getElementById('btn-start');
      const resultCard = document.getElementById('result-card');
      const resultTitle = document.getElementById('result-title');
      const resultMeta = document.getElementById('result-meta');
      const resultChangelog = document.getElementById('result-changelog');
      const progressCard = document.getElementById('progress-card');
      const emptyCard = document.getElementById('empty-card');
      const progressBar = document.getElementById('progress-bar');
      const progressMessage = document.getElementById('progress-message');
      const progressStatus = document.getElementById('progress-status');
      const progressLog = document.getElementById('progress-log');

      let pollTimer = null;
      let latestRelease = null;

      function notify(msg) {
        if (window.inno && typeof inno.msg === 'function') {
          inno.msg(msg);
        } else {
          alert(msg);
        }
      }

      // 渲染 changelog（支持字符串或 [{version,date,changes:[]}] 结构）
      function renderChangelog(changelog) {
        if (!changelog) return '';
        if (typeof changelog === 'string') {
          return '<div class="mb-1 fw-semibold">' + lang.changelog + '</div><div>' +
            changelog.replace(/\n/g, '<br>') + '</div>';
        }
        if (Array.isArray(changelog)) {
          let html = '<div class="mb-1 fw-semibold">' + lang.changelog + '</div>';
          changelog.forEach(function (item) {
            html += '<div class="mb-2"><span class="badge bg-light text-dark me-1">' +
              (item.version || '') + '</span><small class="text-muted">' + (item.date || '') + '</small>';
            if (Array.isArray(item.changes)) {
              html += '<ul class="mb-0 ps-3">';
              item.changes.forEach(function (c) { html += '<li>' + c + '</li>'; });
              html += '</ul>';
            }
            html += '</div>';
          });
          return html;
        }
        return '';
      }

      // 检查更新
      function checkUpdate() {
        btnCheck.disabled = true;
        const original = btnCheck.innerHTML;
        btnCheck.innerHTML = '<i class="bi bi-hourglass-split"></i> ' + lang.checking;

        fetch(urls.check, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        }).then(function (r) { return r.json(); }).then(function (res) {
          btnCheck.disabled = false;
          btnCheck.innerHTML = lang.check_update;

          if (!res.success) {
            notify(res.message || 'error');
            return;
          }

          const data = res.data || {};
          emptyCard.classList.add('d-none');
          resultCard.classList.remove('d-none');

          if (!data.has_update) {
            resultTitle.textContent = lang.up_to_date;
            resultMeta.textContent = '';
            resultChangelog.innerHTML = '';
            btnStart.classList.add('d-none');
            latestRelease = null;
            return;
          }

          latestRelease = data.release || {};
          const version = latestRelease.latest_version || latestRelease.version || '';
          resultTitle.innerHTML = '<span class="text-success"><i class="bi bi-stars"></i> ' +
            '{{ __('console/system_update.latest_version') }}: ' + version + '</span>';

          let meta = [];
          if (latestRelease.released_at) meta.push(lang.released_at + ': ' + latestRelease.released_at);
          if (latestRelease.size) meta.push(lang.package_size + ': ' + (Math.round(latestRelease.size / 1024 / 1024 * 100) / 100) + ' MB');
          resultMeta.textContent = meta.join('  ·  ');

          resultChangelog.innerHTML = renderChangelog(latestRelease.changelog);
          btnStart.classList.remove('d-none');
          btnStart.disabled = false;
        }).catch(function (e) {
          btnCheck.disabled = false;
          btnCheck.innerHTML = lang.check_update;
          notify(String(e));
        });
      }

      // 启动升级
      function startUpdate() {
        const version = latestRelease ? (latestRelease.latest_version || latestRelease.version || '') : '';
        if (!confirm(lang.confirm.replace(':version', version))) {
          return;
        }

        btnStart.disabled = true;
        fetch(urls.start, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        }).then(function (r) { return r.json(); }).then(function (res) {
          if (!res.success) {
            notify(res.message || 'error');
            btnStart.disabled = false;
            return;
          }
          notify(res.message);
          resultCard.classList.add('d-none');
          emptyCard.classList.add('d-none');
          progressCard.classList.remove('d-none');
          startPolling();
        }).catch(function (e) {
          notify(String(e));
          btnStart.disabled = false;
        });
      }

      // 渲染进度
      function renderProgress(p) {
        const percent = parseInt(p.percent || 0, 10);
        progressBar.style.width = percent + '%';
        progressBar.textContent = percent + '%';
        progressMessage.textContent = p.message || '';

        const status = p.status || 'idle';
        progressStatus.textContent = statusText[status] || status;
        progressStatus.className = 'badge ' + ({
          queued: 'bg-secondary',
          running: 'bg-primary',
          success: 'bg-success',
          failed: 'bg-danger',
        }[status] || 'bg-secondary');

        progressBar.classList.toggle('bg-success', status === 'success');
        progressBar.classList.toggle('bg-danger', status === 'failed');
        if (status === 'success' || status === 'failed') {
          progressBar.classList.remove('progress-bar-animated');
        }

        if (Array.isArray(p.logs)) {
          progressLog.textContent = p.logs.map(function (l) {
            return '[' + (l.time || '') + '] ' + (l.message || '');
          }).join('\n');
          progressLog.scrollTop = progressLog.scrollHeight;
        }
      }

      // 轮询进度
      function poll() {
        fetch(urls.progress, { headers: { 'Accept': 'application/json' } })
          .then(function (r) { return r.json(); })
          .then(function (res) {
            const p = res.data || {};
            renderProgress(p);

            if (p.status === 'success') {
              stopPolling();
              notify(p.message || '');
              setTimeout(function () { window.location.reload(); }, 3000);
            } else if (p.status === 'failed') {
              stopPolling();
              notify(p.message || p.error || 'failed');
            }
          })
          .catch(function () { /* 维护模式或网络抖动，下次轮询继续 */ });
      }

      function startPolling() {
        if (pollTimer) return;
        poll();
        pollTimer = setInterval(poll, 2500);
      }

      function stopPolling() {
        if (pollTimer) {
          clearInterval(pollTimer);
          pollTimer = null;
        }
      }

      if (btnCheck) btnCheck.addEventListener('click', checkUpdate);
      if (btnStart) btnStart.addEventListener('click', startUpdate);

      // 页面加载时若已有任务在执行，自动开始轮询
      @if($is_running)
        startPolling();
      @endif
    });
  </script>
@endpush
