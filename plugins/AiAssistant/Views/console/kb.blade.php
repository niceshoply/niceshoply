@extends('console::layouts.app')

@section('title', __('AiAssistant::common.kb_title'))

@section('content')
  <div class="card mb-3"><div class="card-body">
    <p class="text-muted small">{{ __('AiAssistant::common.kb_tip') }}</p>
    <form id="kb-form" class="row g-2">
      <input type="hidden" name="id" id="kb-id">
      <div class="col-md-4"><input name="title" id="kb-title" class="form-control" placeholder="{{ __('AiAssistant::common.title') }}" required></div>
      <div class="col-md-2"><input name="sort" id="kb-sort" type="number" class="form-control" placeholder="{{ __('AiAssistant::common.sort') }}" value="0"></div>
      <div class="col-md-2">
        <select name="is_active" id="kb-active" class="form-select">
          <option value="1">{{ __('AiAssistant::common.active') }}: ✓</option>
          <option value="0">{{ __('AiAssistant::common.active') }}: ✗</option>
        </select>
      </div>
      <div class="col-12"><textarea name="content" id="kb-content" class="form-control" rows="3" placeholder="{{ __('AiAssistant::common.content') }}" required></textarea></div>
      <div class="col-12"><button class="btn btn-primary">{{ __('AiAssistant::common.save') }}</button></div>
    </form>
  </div></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('AiAssistant::common.sort') }}</th><th>{{ __('AiAssistant::common.title') }}</th>
        <th>{{ __('AiAssistant::common.content') }}</th><th>{{ __('AiAssistant::common.active') }}</th><th></th>
      </tr></thead>
      <tbody>
      @forelse($entries as $e)
        <tr>
          <td>{{ $e->sort }}</td>
          <td>{{ $e->title }}</td>
          <td class="small text-muted text-truncate" style="max-width:360px">{{ $e->content }}</td>
          <td>@if($e->is_active)<span class="badge bg-success">✓</span>@else<span class="badge bg-secondary">✗</span>@endif</td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-secondary edit-btn"
              data-id="{{ $e->id }}" data-title="{{ $e->title }}" data-content="{{ $e->content }}"
              data-sort="{{ $e->sort }}" data-active="{{ (int)$e->is_active }}">{{ __('AiAssistant::common.edit') }}</button>
            <button class="btn btn-sm btn-outline-danger del-btn" data-id="{{ $e->id }}">{{ __('AiAssistant::common.del') }}</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('AiAssistant::common.no_kb') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $entries->links() }}</div></div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('kb-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('ai_assistant.kb.store') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });
    document.querySelectorAll('.edit-btn').forEach(b => b.addEventListener('click', function () {
      document.getElementById('kb-id').value = this.dataset.id;
      document.getElementById('kb-title').value = this.dataset.title;
      document.getElementById('kb-content').value = this.dataset.content;
      document.getElementById('kb-sort').value = this.dataset.sort;
      document.getElementById('kb-active').value = this.dataset.active;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }));
    const delBase = '{{ console_route('ai_assistant.kb.destroy', ['id' => '__ID__']) }}';
    document.querySelectorAll('.del-btn').forEach(b => b.addEventListener('click', function () {
      if (!confirm('{{ __('AiAssistant::common.confirm_del') }}')) return;
      fetch(delBase.replace('__ID__', this.dataset.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
    }));
  </script>
@endsection
