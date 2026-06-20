@extends('console::layouts.app')

@section('title', __('SearchPlus::common.title'))

@section('content')
  <div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center">
    <span>{{ __('SearchPlus::common.current_driver') }}：<span class="badge bg-info">{{ $driver }}</span></span>
    @if($driver === 'meilisearch')
      <button id="reindex-btn" class="btn btn-outline-primary">{{ __('SearchPlus::common.reindex') }}</button>
    @endif
  </div></div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card mb-3"><div class="card-header">{{ __('SearchPlus::common.hotwords') }}</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead><tr><th>{{ __('SearchPlus::common.keyword') }}</th><th>{{ __('SearchPlus::common.hits') }}</th><th>{{ __('SearchPlus::common.last_at') }}</th></tr></thead>
            <tbody>
            @forelse($hotWords as $k)
              <tr><td>{{ $k->keyword }}</td><td>{{ $k->hits }}</td><td class="small text-muted">{{ $k->last_at }}</td></tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted py-3">{{ __('SearchPlus::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card"><div class="card-header">{{ __('SearchPlus::common.noresults') }}</div>
        <div class="card-body">
          @forelse($noResults as $k)
            <span class="badge bg-warning text-dark me-1 mb-1">{{ $k->keyword }} ({{ $k->hits }})</span>
          @empty
            <span class="text-muted">{{ __('SearchPlus::common.no_data') }}</span>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card mb-3"><div class="card-header">{{ __('SearchPlus::common.synonyms') }}</div>
        <div class="card-body">
          <p class="text-muted small">{{ __('SearchPlus::common.syn_tip') }}</p>
          <form id="syn-form" class="row g-2 mb-3">
            <div class="col-9"><input name="terms" class="form-control" placeholder="手机,智能手机,cellphone" required></div>
            <div class="col-3"><button class="btn btn-primary w-100">{{ __('SearchPlus::common.add') }}</button></div>
          </form>
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead><tr><th>{{ __('SearchPlus::common.terms') }}</th><th></th></tr></thead>
            <tbody>
            @forelse($synonyms as $s)
              <tr><td>{{ $s->terms }}</td><td class="text-end"><button class="btn btn-sm btn-outline-danger del-btn" data-id="{{ $s->id }}">{{ __('SearchPlus::common.del') }}</button></td></tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted py-3">{{ __('SearchPlus::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <div class="card"><div class="card-header">{{ __('SearchPlus::common.api_title') }}</div>
        <div class="card-body small">
          <pre class="bg-light p-2 mb-0" style="white-space:pre-wrap">GET /search-plus?q=关键词
GET /search-plus/hotwords
GET /search-plus/suggest?q=前缀</pre>
        </div>
      </div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('syn-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('search_plus.synonym.store') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });
    const delBase = '{{ console_route('search_plus.synonym.destroy', ['id' => '__ID__']) }}';
    document.querySelectorAll('.del-btn').forEach(btn => btn.addEventListener('click', function () {
      if (!confirm('{{ __('SearchPlus::common.confirm_del') }}')) return;
      fetch(delBase.replace('__ID__', this.dataset.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
    }));
    const reindexBtn = document.getElementById('reindex-btn');
    if (reindexBtn) reindexBtn.addEventListener('click', function () {
      this.disabled = true;
      fetch('{{ console_route('search_plus.reindex') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { alert(res.message || 'done'); this.disabled = false; });
    });
  </script>
@endsection
