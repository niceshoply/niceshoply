@extends('console::layouts.app')

@section('title', __('WechatMp::common.menu'))

@section('content')
  <div class="alert alert-info">
    {{ __('WechatMp::common.serve_url_tip') }} <code>{{ $serveUrl }}</code>
  </div>

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">{{ __('WechatMp::common.create') }}</div>
        <div class="card-body">
          <form id="r-form">
            <input type="hidden" name="id" id="r-id">
            <div class="mb-2">
              <label class="form-label">{{ __('WechatMp::common.match_type') }}</label>
              <select name="match_type" id="r-type" class="form-select">
                <option value="equal">{{ __('WechatMp::common.match_equal') }}</option>
                <option value="contains">{{ __('WechatMp::common.match_contains') }}</option>
                <option value="default">{{ __('WechatMp::common.match_default') }}</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">{{ __('WechatMp::common.keyword') }}</label>
              <input name="keyword" id="r-keyword" class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">{{ __('WechatMp::common.content') }}</label>
              <textarea name="content" id="r-content" class="form-control" rows="4" required></textarea>
            </div>
            <input type="hidden" name="is_active" value="1">
            <button type="submit" class="btn btn-primary w-100">{{ __('WechatMp::common.submit') }}</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card"><div class="card-body table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead><tr>
            <th>#</th><th>{{ __('WechatMp::common.match_type') }}</th><th>{{ __('WechatMp::common.keyword') }}</th>
            <th>{{ __('WechatMp::common.content') }}</th><th>{{ __('WechatMp::common.is_active') }}</th>
            <th class="text-end">{{ __('WechatMp::common.actions') }}</th>
          </tr></thead>
          <tbody>
          @forelse($replies as $r)
            <tr>
              <td>{{ $r->id }}</td>
              <td>{{ __('WechatMp::common.match_'.$r->match_type) }}</td>
              <td>{{ $r->keyword }}</td>
              <td class="text-truncate" style="max-width:280px">{{ $r->content }}</td>
              <td>{!! $r->is_active ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-secondary">OFF</span>' !!}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary btn-edit" data-json='@json($r)'>{{ __('WechatMp::common.edit') }}</button>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $r->id }}">{{ __('WechatMp::common.delete') }}</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('WechatMp::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div></div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    const base = '{{ console_route('wechat_mp.index') }}';

    document.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', function () {
      const d = JSON.parse(this.dataset.json);
      document.getElementById('r-id').value = d.id;
      document.getElementById('r-type').value = d.match_type;
      document.getElementById('r-keyword').value = d.keyword || '';
      document.getElementById('r-content').value = d.content;
    }));

    document.getElementById('r-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch(base, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this) })
        .then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });

    document.querySelectorAll('.btn-del').forEach(b => b.addEventListener('click', function () {
      if (!confirm('?')) return;
      const fd = new FormData(); fd.append('_method', 'DELETE');
      fetch(base + '/' + this.dataset.id, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd })
        .then(r => r.json()).then(() => location.reload());
    }));
  </script>
@endsection
