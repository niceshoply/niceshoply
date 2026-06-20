@extends('console::layouts.app')

@section('title', $sale->exists ? __('FlashSale::common.edit') : __('FlashSale::common.create'))

@section('content')
  <div class="card">
    <div class="card-body">
      <form id="flash-form">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('FlashSale::common.name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ $sale->name }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('FlashSale::common.start_at') }}</label>
            <input type="datetime-local" name="start_at" class="form-control" value="{{ $sale->start_at?->format('Y-m-d\TH:i') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('FlashSale::common.end_at') }}</label>
            <input type="datetime-local" name="end_at" class="form-control" value="{{ $sale->end_at?->format('Y-m-d\TH:i') }}">
          </div>
          <div class="col-12">
            <div class="form-check">
              <input type="checkbox" name="active" value="1" class="form-check-input" id="active" @checked($sale->active)>
              <label class="form-check-label" for="active">{{ __('FlashSale::common.active') }}</label>
            </div>
          </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>{{ __('FlashSale::common.items') }}</strong>
          <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">
            <i class="bi bi-plus-lg"></i> {{ __('FlashSale::common.add_item') }}
          </button>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered align-middle" id="items-table">
            <thead>
            <tr>
              <th>{{ __('FlashSale::common.sku_id') }}</th>
              <th>{{ __('FlashSale::common.product_id') }}</th>
              <th>{{ __('FlashSale::common.sale_price') }}</th>
              <th>{{ __('FlashSale::common.qty_limit') }}</th>
              <th></th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-primary">{{ __('FlashSale::common.saved') }}</button>
          <a href="{{ console_route('flash_sales.index') }}" class="btn btn-light">{{ __('FlashSale::common.back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    const existing = @json($sale->exists ? $sale->items->map(fn($i) => ['sku_id'=>$i->sku_id,'product_id'=>$i->product_id,'sale_price'=>$i->sale_price,'qty_limit'=>$i->qty_limit]) : []);
    const tbody = document.querySelector('#items-table tbody');

    function addRow(item) {
      item = item || { sku_id: '', product_id: '', sale_price: '', qty_limit: 0 };
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input type="number" class="form-control form-control-sm i-sku" value="${item.sku_id}"></td>
        <td><input type="number" class="form-control form-control-sm i-pid" value="${item.product_id}"></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm i-price" value="${item.sale_price}"></td>
        <td><input type="number" class="form-control form-control-sm i-qty" value="${item.qty_limit}"></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger i-del">&times;</button></td>`;
      tr.querySelector('.i-del').addEventListener('click', () => tr.remove());
      tbody.appendChild(tr);
    }

    existing.forEach(addRow);
    if (!existing.length) addRow();
    document.getElementById('btn-add-item').addEventListener('click', () => addRow());

    document.getElementById('flash-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      if (!form.get('active')) form.set('active', '0');
      tbody.querySelectorAll('tr').forEach((tr, idx) => {
        const sku = tr.querySelector('.i-sku').value;
        if (!sku) return;
        form.set(`items[${idx}][sku_id]`, sku);
        form.set(`items[${idx}][product_id]`, tr.querySelector('.i-pid').value || 0);
        form.set(`items[${idx}][sale_price]`, tr.querySelector('.i-price').value || 0);
        form.set(`items[${idx}][qty_limit]`, tr.querySelector('.i-qty').value || 0);
      });
      const isEdit = {{ $sale->exists ? 'true' : 'false' }};
      const url = isEdit ? '{{ $sale->exists ? console_route('flash_sales.update', $sale->id) : '' }}' : '{{ console_route('flash_sales.store') }}';
      if (isEdit) form.append('_method', 'PUT');
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(res => {
        if (res.code === 0 || res.success) location.href = '{{ console_route('flash_sales.index') }}';
        else alert(res.message || 'Error');
      });
    });
  </script>
@endsection
