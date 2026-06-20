@extends('console::layouts.app')

@section('title', __('MultiWarehouse::common.title'))

@section('content')
  <p class="text-muted small">{{ __('MultiWarehouse::common.tip') }}</p>

  <div class="d-flex gap-2 mb-3">
    <a href="{{ console_route('multi_warehouse.stock') }}" class="btn btn-outline-primary btn-sm">{{ __('MultiWarehouse::common.manage_stock') }}</a>
    <button id="sync-btn" class="btn btn-outline-secondary btn-sm">{{ __('MultiWarehouse::common.sync_all') }}</button>
  </div>

  <div class="card mb-3"><div class="card-header">{{ __('MultiWarehouse::common.warehouses') }}</div>
    <div class="card-body">
      <form id="wh-form" class="row g-2 mb-3">
        <input type="hidden" name="id" id="w-id">
        <div class="col-md-2"><input name="name" class="form-control" placeholder="{{ __('MultiWarehouse::common.name') }}" required></div>
        <div class="col-md-2"><input name="code" class="form-control" placeholder="{{ __('MultiWarehouse::common.code') }}" required></div>
        <div class="col-md-2"><input name="province" class="form-control" placeholder="{{ __('MultiWarehouse::common.province') }}"></div>
        <div class="col-md-2"><input name="city" class="form-control" placeholder="{{ __('MultiWarehouse::common.city') }}"></div>
        <div class="col-md-2"><input name="address" class="form-control" placeholder="{{ __('MultiWarehouse::common.address') }}"></div>
        <div class="col-md-1">
          <select name="is_default" class="form-select"><option value="0">-</option><option value="1">{{ __('MultiWarehouse::common.default') }}</option></select>
        </div>
        <div class="col-md-1"><button class="btn btn-primary w-100">{{ __('MultiWarehouse::common.saved') }}</button></div>
      </form>
      <table class="table table-sm table-bordered mb-0">
        <thead><tr><th>{{ __('MultiWarehouse::common.name') }}</th><th>{{ __('MultiWarehouse::common.code') }}</th><th>{{ __('MultiWarehouse::common.province') }}/{{ __('MultiWarehouse::common.city') }}</th><th>{{ __('MultiWarehouse::common.default') }}</th></tr></thead>
        <tbody>
        @forelse($warehouses as $w)
          <tr><td>{{ $w->name }}</td><td>{{ $w->code }}</td><td>{{ $w->province }}/{{ $w->city }}</td><td>@if($w->is_default)✓@endif</td></tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted py-3">{{ __('MultiWarehouse::common.no_data') }}</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-3"><div class="card-header">{{ __('MultiWarehouse::common.transfer') }}</div>
    <div class="card-body">
      <form id="tr-form" class="row g-2">
        <div class="col-md-2"><input name="from_warehouse_id" type="number" class="form-control" placeholder="{{ __('MultiWarehouse::common.from') }} ID" required></div>
        <div class="col-md-2"><input name="to_warehouse_id" type="number" class="form-control" placeholder="{{ __('MultiWarehouse::common.to') }} ID" required></div>
        <div class="col-md-2"><input name="sku_id" type="number" class="form-control" placeholder="{{ __('MultiWarehouse::common.sku_id') }}" required></div>
        <div class="col-md-2"><input name="quantity" type="number" min="1" class="form-control" placeholder="{{ __('MultiWarehouse::common.quantity') }}" required></div>
        <div class="col-md-3"><input name="remark" class="form-control" placeholder="{{ __('MultiWarehouse::common.remark') }}"></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">{{ __('MultiWarehouse::common.transfer') }}</button></div>
      </form>
    </div>
  </div>

  <div class="card"><div class="card-header">{{ __('MultiWarehouse::common.transfers') }}</div>
    <div class="card-body table-responsive">
      <table class="table table-sm table-bordered mb-0">
        <thead><tr><th>{{ __('MultiWarehouse::common.from') }}</th><th>{{ __('MultiWarehouse::common.to') }}</th><th>SKU</th><th>Qty</th><th>Time</th></tr></thead>
        <tbody>
        @forelse($transfers as $t)
          <tr><td>{{ $t->from_warehouse_id }}</td><td>{{ $t->to_warehouse_id }}</td><td>{{ $t->sku_id }}</td><td>{{ $t->quantity }}</td><td class="small">{{ $t->created_at }}</td></tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted py-3">{{ __('MultiWarehouse::common.no_data') }}</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('wh-form').addEventListener('submit', e => {
      e.preventDefault();
      fetch('{{ console_route('multi_warehouse.warehouse.store') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new FormData(e.target) })
        .then(r=>r.json()).then(res=>{ if(res.success) location.reload(); else alert(res.message); });
    });
    document.getElementById('tr-form').addEventListener('submit', e => {
      e.preventDefault();
      fetch('{{ console_route('multi_warehouse.transfer') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new FormData(e.target) })
        .then(r=>r.json()).then(res=>{ if(res.success) location.reload(); else alert(res.message); });
    });
    document.getElementById('sync-btn').addEventListener('click', () => {
      fetch('{{ console_route('multi_warehouse.sync') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} })
        .then(r=>r.json()).then(res=> alert(res.message));
    });
  </script>
@endsection
