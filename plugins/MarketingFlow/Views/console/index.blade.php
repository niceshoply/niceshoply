@extends('console::layouts.app')

@section('title', __('MarketingFlow::common.title'))

@section('content')
  <div class="card mb-3"><div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <span class="me-3">{{ __('MarketingFlow::common.pending') }}：<span class="badge bg-warning text-dark">{{ $pending }}</span></span>
      <span>{{ __('MarketingFlow::common.sent') }}：<span class="badge bg-success">{{ $sent }}</span></span>
    </div>
    <button id="run-btn" class="btn btn-outline-primary btn-sm">{{ __('MarketingFlow::common.run_now') }}</button>
  </div></div>

  <div class="card mb-3"><div class="card-body">
    <p class="text-muted small">{{ __('MarketingFlow::common.tip') }}</p>
    <form id="flow-form" class="row g-2">
      <input type="hidden" name="id" id="f-id">
      <div class="col-md-3"><input name="name" id="f-name" class="form-control" placeholder="{{ __('MarketingFlow::common.name') }}" required></div>
      <div class="col-md-3">
        <select name="trigger_event" id="f-trigger" class="form-select">
          @foreach($events as $ev)
            <option value="{{ $ev }}">{{ __('MarketingFlow::common.event_'.$ev) }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2"><input name="delay_minutes" id="f-delay" type="number" min="0" class="form-control" placeholder="{{ __('MarketingFlow::common.delay') }}" value="0"></div>
      <div class="col-md-2">
        <select name="is_active" id="f-active" class="form-select">
          <option value="1">{{ __('MarketingFlow::common.active') }}: ✓</option>
          <option value="0">{{ __('MarketingFlow::common.active') }}: ✗</option>
        </select>
      </div>
      <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('MarketingFlow::common.save') }}</button></div>
      <div class="col-md-4"><input name="title" id="f-title" class="form-control" placeholder="{{ __('MarketingFlow::common.msg_title') }}" required></div>
      <div class="col-md-8"><input name="content" id="f-content" class="form-control" placeholder="{{ __('MarketingFlow::common.content') }} ({order_no})"></div>
    </form>
  </div></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('MarketingFlow::common.name') }}</th><th>{{ __('MarketingFlow::common.trigger') }}</th>
        <th>{{ __('MarketingFlow::common.delay') }}</th><th>{{ __('MarketingFlow::common.msg_title') }}</th>
        <th>{{ __('MarketingFlow::common.active') }}</th><th>{{ __('MarketingFlow::common.sent_count') }}</th><th></th>
      </tr></thead>
      <tbody>
      @forelse($flows as $f)
        <tr>
          <td>{{ $f->name }}</td>
          <td>{{ __('MarketingFlow::common.event_'.$f->trigger_event) }}</td>
          <td>{{ $f->delay_minutes }}</td>
          <td>{{ $f->title }}</td>
          <td>@if($f->is_active)<span class="badge bg-success">✓</span>@else<span class="badge bg-secondary">✗</span>@endif</td>
          <td>{{ $f->sent_count }}</td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-secondary edit-btn"
              data-id="{{ $f->id }}" data-name="{{ $f->name }}" data-trigger="{{ $f->trigger_event }}"
              data-delay="{{ $f->delay_minutes }}" data-title="{{ $f->title }}" data-content="{{ $f->content }}"
              data-active="{{ (int)$f->is_active }}">{{ __('MarketingFlow::common.edit') }}</button>
            <button class="btn btn-sm btn-outline-danger del-btn" data-id="{{ $f->id }}">{{ __('MarketingFlow::common.del') }}</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('MarketingFlow::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div></div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('flow-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('marketing_flow.store') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });
    document.querySelectorAll('.edit-btn').forEach(b => b.addEventListener('click', function () {
      const d = this.dataset;
      document.getElementById('f-id').value = d.id;
      document.getElementById('f-name').value = d.name;
      document.getElementById('f-trigger').value = d.trigger;
      document.getElementById('f-delay').value = d.delay;
      document.getElementById('f-title').value = d.title;
      document.getElementById('f-content').value = d.content;
      document.getElementById('f-active').value = d.active;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }));
    const delBase = '{{ console_route('marketing_flow.destroy', ['id' => '__ID__']) }}';
    document.querySelectorAll('.del-btn').forEach(b => b.addEventListener('click', function () {
      if (!confirm('{{ __('MarketingFlow::common.confirm_del') }}')) return;
      fetch(delBase.replace('__ID__', this.dataset.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
    }));
    document.getElementById('run-btn').addEventListener('click', function () {
      this.disabled = true;
      fetch('{{ console_route('marketing_flow.run') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { alert(res.message); location.reload(); });
    });
  </script>
@endsection
