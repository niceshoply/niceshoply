@extends('console::layouts.app')

@section('title', __('OfflineRedeem::common.title'))

@section('content')
  <p class="text-muted small">{{ __('OfflineRedeem::common.tip') }}</p>

  <div class="card mb-3"><div class="card-body">
    <form id="gen-form" class="row g-2">
      <div class="col-md-4"><input name="title" class="form-control" placeholder="{{ __('OfflineRedeem::common.title_label') }}" required></div>
      <div class="col-md-2"><input name="type" class="form-control" placeholder="{{ __('OfflineRedeem::common.type') }}" value="voucher"></div>
      <div class="col-md-2"><input name="customer_id" type="number" class="form-control" placeholder="{{ __('OfflineRedeem::common.customer_id') }}"></div>
      <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('OfflineRedeem::common.generate') }}</button></div>
    </form>
  </div></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered mb-0">
      <thead><tr><th>{{ __('OfflineRedeem::common.code') }}</th><th>{{ __('OfflineRedeem::common.title_label') }}</th><th>{{ __('OfflineRedeem::common.status') }}</th><th>{{ __('OfflineRedeem::common.redeemed_at') }}</th></tr></thead>
      <tbody>
      @forelse($codes as $c)
        <tr><td><code>{{ $c->code }}</code></td><td>{{ $c->title }}</td><td>{{ $c->status }}</td><td class="small">{{ $c->redeemed_at }}</td></tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('OfflineRedeem::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $codes->links() }}</div></div>

  <div class="card mt-3"><div class="card-header">{{ __('OfflineRedeem::common.api_title') }}</div>
    <div class="card-body small"><pre class="bg-light p-2 mb-0">POST /redeem/verify  {code}
POST /redeem/use      {code, staff_token}</pre></div>
  </div>

  <script>
    document.getElementById('gen-form').addEventListener('submit', e => {
      e.preventDefault();
      fetch('{{ console_route('offline_redeem.generate') }}', { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:new FormData(e.target) })
        .then(r=>r.json()).then(res=>{ alert(res.message + (res.data?.code ? ': '+res.data.code : '')); if(res.success) location.reload(); });
    });
  </script>
@endsection
