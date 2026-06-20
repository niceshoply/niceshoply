@extends('console::layouts.app')

@section('title', __('EmailMarketing::common.menu_campaigns'))

@section('content')
  <div class="mb-3 d-flex justify-content-between">
    <button class="btn btn-primary" id="btn-new">{{ __('EmailMarketing::common.create') }}</button>
    <a href="{{ console_route('email_marketing.subscribers') }}" class="btn btn-outline-secondary btn-sm">{{ __('EmailMarketing::common.menu_subscribers') }}</a>
  </div>

  <div class="card mb-3 d-none" id="form-card">
    <div class="card-body">
      <form id="c-form">
        <input type="hidden" name="id" id="c-id">
        <div class="mb-2"><label class="form-label">{{ __('EmailMarketing::common.subject') }}</label><input name="subject" id="c-subject" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">{{ __('EmailMarketing::common.body') }}</label><textarea name="body" id="c-body" class="form-control" rows="8" required></textarea></div>
        <div class="row g-2">
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __('EmailMarketing::common.target') }}</label>
            <select name="target" id="c-target" class="form-select">
              <option value="subscribers">{{ __('EmailMarketing::common.target_subscribers') }}</option>
              <option value="customers">{{ __('EmailMarketing::common.target_customers') }}</option>
            </select>
          </div>
          <div class="col-md-6 mb-2">
            <label class="form-label">{{ __('EmailMarketing::common.scheduled_at') }}</label>
            <input name="scheduled_at" id="c-sched" type="datetime-local" class="form-control">
          </div>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('EmailMarketing::common.submit') }}</button>
      </form>
    </div>
  </div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>#</th><th>{{ __('EmailMarketing::common.subject') }}</th><th>{{ __('EmailMarketing::common.target') }}</th>
        <th>{{ __('EmailMarketing::common.status') }}</th><th>{{ __('EmailMarketing::common.progress') }}</th>
        <th class="text-end">{{ __('EmailMarketing::common.edit') }}</th>
      </tr></thead>
      <tbody>
      @forelse($campaigns as $c)
        <tr>
          <td>{{ $c->id }}</td>
          <td>{{ $c->subject }}</td>
          <td>{{ $c->target === 'customers' ? __('EmailMarketing::common.target_customers') : __('EmailMarketing::common.target_subscribers') }}</td>
          <td>
            @php($m = ['draft'=>'bg-secondary','sending'=>'bg-info','sent'=>'bg-success'])
            <span class="badge {{ $m[$c->status] ?? 'bg-secondary' }}">{{ __('EmailMarketing::common.status_'.$c->status) }}</span>
          </td>
          <td>{{ $c->sent_count }}/{{ $c->total }} @if($c->fail_count) <span class="text-danger">(-{{ $c->fail_count }})</span>@endif</td>
          <td class="text-end">
            @if($c->status !== 'sent')
              <button class="btn btn-sm btn-outline-primary btn-edit" data-json='@json($c)'>{{ __('EmailMarketing::common.edit') }}</button>
              <button class="btn btn-sm btn-success btn-send" data-id="{{ $c->id }}">{{ __('EmailMarketing::common.send') }}</button>
            @endif
            <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $c->id }}">{{ __('EmailMarketing::common.delete') }}</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('EmailMarketing::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $campaigns->links() }}</div></div>

  <script>
    const csrf = '{{ csrf_token() }}';
    const base = '{{ console_route('email_marketing.campaigns') }}';
    const card = document.getElementById('form-card');

    document.getElementById('btn-new').addEventListener('click', () => {
      card.classList.remove('d-none');
      document.getElementById('c-form').reset();
      document.getElementById('c-id').value = '';
    });

    document.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', function () {
      const d = JSON.parse(this.dataset.json);
      card.classList.remove('d-none');
      document.getElementById('c-id').value = d.id;
      document.getElementById('c-subject').value = d.subject;
      document.getElementById('c-body').value = d.body;
      document.getElementById('c-target').value = d.target;
      document.getElementById('c-sched').value = d.scheduled_at ? d.scheduled_at.substring(0,16) : '';
    }));

    document.getElementById('c-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch(base, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this) })
        .then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });

    document.querySelectorAll('.btn-send').forEach(b => b.addEventListener('click', function () {
      if (!confirm('{{ __('EmailMarketing::common.confirm_send') }}')) return;
      fetch(base + '/' + this.dataset.id + '/send', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
        .then(r => r.json()).then(res => { alert(res.message); location.reload(); });
    }));

    document.querySelectorAll('.btn-del').forEach(b => b.addEventListener('click', function () {
      if (!confirm('?')) return;
      const fd = new FormData(); fd.append('_method', 'DELETE');
      fetch(base + '/' + this.dataset.id, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd })
        .then(r => r.json()).then(() => location.reload());
    }));
  </script>
@endsection
