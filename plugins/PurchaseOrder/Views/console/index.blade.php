@extends('console::layouts.app')

@section('title', __('PurchaseOrder::common.title'))

@section('content')
  <p class="text-muted small">{{ __('PurchaseOrder::common.tip') }}</p>

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card mb-3"><div class="card-header">{{ __('PurchaseOrder::common.add_supplier') }}</div>
        <div class="card-body">
          <form id="sup-form" class="row g-2">
            <div class="col-12"><input name="name" class="form-control" placeholder="{{ __('PurchaseOrder::common.supplier_name') }}" required></div>
            <div class="col-6"><input name="contact" class="form-control" placeholder="{{ __('PurchaseOrder::common.contact') }}"></div>
            <div class="col-6"><input name="phone" class="form-control" placeholder="{{ __('PurchaseOrder::common.phone') }}"></div>
            <div class="col-12"><button class="btn btn-primary btn-sm">{{ __('PurchaseOrder::common.add_supplier') }}</button></div>
          </form>
          <hr>
          @forelse($suppliers as $s)
            <div class="small mb-1"><strong>#{{ $s->id }}</strong> {{ $s->name }} · {{ $s->phone }}</div>
          @empty
            <span class="text-muted">{{ __('PurchaseOrder::common.no_data') }}</span>
          @endforelse
        </div>
      </div>

      <div class="card"><div class="card-header">{{ __('PurchaseOrder::common.suggestions') }}</div>
        <div class="card-body table-responsive">
          <table class="table table-sm mb-0">
            <thead><tr><th>{{ __('PurchaseOrder::common.sku_code') }}</th><th>{{ __('PurchaseOrder::common.current_qty') }}</th><th>{{ __('PurchaseOrder::common.suggest_qty') }}</th></tr></thead>
            <tbody>
            @forelse($suggestions as $sg)
              <tr><td>{{ $sg['sku_code'] }}</td><td>{{ $sg['quantity'] }}</td><td>{{ $sg['suggest'] }}</td></tr>
            @empty
              <tr><td colspan="3" class="text-muted text-center">{{ __('PurchaseOrder::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card mb-3"><div class="card-header">{{ __('PurchaseOrder::common.create_po') }}</div>
        <div class="card-body">
          <form id="po-form" class="row g-2">
            <div class="col-md-3"><input name="supplier_id" type="number" class="form-control" placeholder="{{ __('PurchaseOrder::common.supplier_id') }}" required></div>
            <div class="col-md-3"><input name="warehouse_id" type="number" class="form-control" placeholder="{{ __('PurchaseOrder::common.warehouse_id') }}"></div>
            <div class="col-md-6"><input name="items" class="form-control" placeholder="12:50:10.5, 15:30:8" required></div>
            <div class="col-12"><input name="remark" class="form-control" placeholder="{{ __('PurchaseOrder::common.remark') }}"></div>
            <div class="col-12"><button class="btn btn-primary">{{ __('PurchaseOrder::common.create_po') }}</button></div>
          </form>
        </div>
      </div>

      <div class="card"><div class="card-header">{{ __('PurchaseOrder::common.orders') }}</div>
        <div class="card-body table-responsive">
          <table class="table table-bordered mb-0">
            <thead><tr>
              <th>{{ __('PurchaseOrder::common.po_number') }}</th><th>{{ __('PurchaseOrder::common.status') }}</th>
              <th>{{ __('PurchaseOrder::common.total') }}</th><th>Items</th><th></th>
            </tr></thead>
            <tbody>
            @forelse($orders as $o)
              <tr>
                <td>{{ $o->po_number }}</td><td>{{ $o->status }}</td><td>{{ currency_format($o->total) }}</td>
                <td class="small">{{ $o->items->count() }}</td>
                <td class="text-end">
                  @if($o->status !== 'received')
                    <button class="btn btn-sm btn-outline-success recv-btn" data-id="{{ $o->id }}">{{ __('PurchaseOrder::common.receive') }}</button>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">{{ __('PurchaseOrder::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('sup-form').addEventListener('submit', e => {
      e.preventDefault();
      fetch('{{ console_route('purchase_order.supplier.store') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new FormData(e.target) })
        .then(r=>r.json()).then(res=>{ if(res.success) location.reload(); else alert(res.message); });
    });
    document.getElementById('po-form').addEventListener('submit', e => {
      e.preventDefault();
      fetch('{{ console_route('purchase_order.store') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new FormData(e.target) })
        .then(r=>r.json()).then(res=>{ if(res.success) location.reload(); else alert(res.message); });
    });
    const recvBase = '{{ console_route('purchase_order.receive', ['id' => '__ID__']) }}';
    document.querySelectorAll('.recv-btn').forEach(b => b.addEventListener('click', function() {
      if (!confirm('{{ __('PurchaseOrder::common.confirm_receive') }}')) return;
      fetch(recvBase.replace('__ID__', this.dataset.id), { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} })
        .then(r=>r.json()).then(res=>{ if(res.success) location.reload(); else alert(res.message); });
    }));
  </script>
@endsection
