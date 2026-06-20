@extends('console::layouts.app')

@section('title', __('Backup::common.title'))

@section('content')
  <div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center">
    <span class="text-muted small">{{ __('Backup::common.tip') }}</span>
    <button id="run-btn" class="btn btn-primary flex-shrink-0">
      <i class="bi bi-database-down"></i> {{ __('Backup::common.run') }}
    </button>
  </div></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('Backup::common.name') }}</th><th>{{ __('Backup::common.size') }}</th>
        <th>{{ __('Backup::common.time') }}</th><th class="text-end">&nbsp;</th>
      </tr></thead>
      <tbody>
      @forelse($backups as $b)
        <tr>
          <td><code>{{ $b['name'] }}</code></td>
          <td>{{ number_format($b['size'] / 1024, 1) }} KB</td>
          <td>{{ $b['time'] }}</td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary" href="{{ console_route('backup.download') }}?name={{ urlencode($b['name']) }}">{{ __('Backup::common.download') }}</a>
            <button class="btn btn-sm btn-outline-danger del-btn" data-name="{{ $b['name'] }}">{{ __('Backup::common.del') }}</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('Backup::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div></div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('run-btn').addEventListener('click', function () {
      this.disabled = true;
      fetch('{{ console_route('backup.create') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { alert(res.message || 'done'); if (res.success) location.reload(); else this.disabled = false; })
        .catch(() => { this.disabled = false; });
    });
    document.querySelectorAll('.del-btn').forEach(btn => btn.addEventListener('click', function () {
      if (!confirm('{{ __('Backup::common.confirm_del') }}')) return;
      const fd = new FormData(); fd.append('name', this.dataset.name);
      fetch('{{ console_route('backup.destroy') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    }));
  </script>
@endsection
