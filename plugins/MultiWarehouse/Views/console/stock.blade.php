@extends('console::layouts.app')

@section('title', __('MultiWarehouse::common.stock_title'))

@section('content')
  <div class="mb-3"><a href="{{ console_route('multi_warehouse.index') }}" class="btn btn-sm btn-outline-secondary">← {{ __('MultiWarehouse::common.title') }}</a></div>

  <div class="card mb-3"><div class="card-body">
    <form id="stock-form" class="row g-2">
      <div class="col-md-3">
        <select name="warehouse_id" class="form-select" required>
          @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }} ({{ $w->code }})</option>@endforeach
        </select>
      </div>
      <div class="col-md-3"><input name="sku_id" type="number" class="form-control" placeholder="{{ __('MultiWarehouse::common.sku_id') }}" required></div>
      <div class="col-md-2"><input name="quantity" type="number" min="0" class="form-control" placeholder="{{ __('MultiWarehouse::common.quantity') }}" required></div>
      <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('MultiWarehouse::common.saved') }}</button></div>
    </form>
  </div></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered mb-0">
      <thead><tr><th>{{ __('MultiWarehouse::common.name') }}</th><th>SKU ID</th><th>{{ __('MultiWarehouse::common.sku_code') }}</th><th>{{ __('MultiWarehouse::common.quantity') }}</th></tr></thead>
      <tbody>
      @forelse($stocks as $s)
        <tr><td>{{ $warehouses->firstWhere('id',$s->warehouse_id)?->name ?? $s->warehouse_id }}</td><td>{{ $s->sku_id }}</td><td>{{ $skuMap[$s->sku_id] ?? '-' }}</td><td>{{ $s->quantity }}</td></tr>
      @empty
        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('MultiWarehouse::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div></div>

  <script>
    document.getElementById('stock-form').addEventListener('submit', e => {
      e.preventDefault();
      fetch('{{ console_route('multi_warehouse.stock.set') }}', { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body:new FormData(e.target) })
        .then(r=>r.json()).then(res=>{ if(res.success) location.reload(); else alert(res.message); });
    });
  </script>
@endsection
