@extends('console::layouts.app')

@section('title', __('SmsMarketing::common.title'))

@section('content')
  <p class="text-muted small">{{ __('SmsMarketing::common.tip') }}</p>

  <div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card text-center"><div class="card-body">
      <div class="text-muted small">{{ __('SmsMarketing::common.recipients') }}</div>
      <div class="fs-3 fw-bold">{{ number_format($recipientCount) }}</div>
    </div></div></div>
    <div class="col-md-6"><div class="card text-center"><div class="card-body">
      <div class="text-muted small">{{ __('SmsMarketing::common.unsub_count') }}</div>
      <div class="fs-3 fw-bold">{{ number_format($unsubCount) }}</div>
    </div></div></div>
  </div>

  <div class="card mb-3"><div class="card-body">
    <form id="camp-form" class="row g-2">
      <input type="hidden" name="id" id="c-id">
      <div class="col-md-3"><input name="name" id="c-name" class="form-control" placeholder="{{ __('SmsMarketing::common.name') }}" required></div>
      <div class="col-md-3"><input name="template_id" id="c-tpl" class="form-control" placeholder="{{ __('SmsMarketing::common.template_id') }}" required></div>
      <div class="col-md-4"><input name="template_data" id="c-data" class="form-control" placeholder='{{ __('SmsMarketing::common.template_data') }} {"code":"SALE"}'></div>
      <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('SmsMarketing::common.save') }}</button></div>
    </form>
  </div></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('SmsMarketing::common.name') }}</th><th>{{ __('SmsMarketing::common.template_id') }}</th>
        <th>{{ __('SmsMarketing::common.status') }}</th><th>{{ __('SmsMarketing::common.progress') }}</th><th></th>
      </tr></thead>
      <tbody>
      @forelse($campaigns as $c)
        <tr>
          <td>{{ $c->name }}</td>
          <td><code>{{ $c->template_id }}</code></td>
          <td>{{ $c->status }}</td>
          <td>{{ $c->sent_count }}/{{ $c->total }} @if($c->fail_count)(fail {{ $c->fail_count }})@endif</td>
          <td class="text-end text-nowrap">
            @if($c->status !== 'sent')
              <button class="btn btn-sm btn-outline-primary send-btn" data-id="{{ $c->id }}">{{ __('SmsMarketing::common.send') }}</button>
            @endif
            <button class="btn btn-sm btn-outline-danger del-btn" data-id="{{ $c->id }}">{{ __('SmsMarketing::common.del') }}</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('SmsMarketing::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div></div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('camp-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('sms_marketing.store') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });
    const sendBase = '{{ console_route('sms_marketing.send', ['id' => '__ID__']) }}';
    document.querySelectorAll('.send-btn').forEach(b => b.addEventListener('click', function () {
      if (!confirm('{{ __('SmsMarketing::common.confirm_send') }}')) return;
      this.disabled = true;
      fetch(sendBase.replace('__ID__', this.dataset.id), {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { alert(res.message); location.reload(); });
    }));
    const delBase = '{{ console_route('sms_marketing.destroy', ['id' => '__ID__']) }}';
    document.querySelectorAll('.del-btn').forEach(b => b.addEventListener('click', function () {
      if (!confirm('{{ __('SmsMarketing::common.confirm_del') }}')) return;
      fetch(delBase.replace('__ID__', this.dataset.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
    }));
  </script>
@endsection
