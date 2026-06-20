@extends('console::layouts.app')

@section('title', $activity->exists ? __('Presale::common.edit') : __('Presale::common.create'))

@section('content')
  <div class="card">
    <div class="card-body">
      <form id="presale-form">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('Presale::common.name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ $activity->name }}" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('Presale::common.start_at') }}</label>
            <input type="datetime-local" name="start_at" class="form-control" value="{{ $activity->start_at?->format('Y-m-d\TH:i') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('Presale::common.end_at') }}</label>
            <input type="datetime-local" name="end_at" class="form-control" value="{{ $activity->end_at?->format('Y-m-d\TH:i') }}">
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('Presale::common.ship_date') }}</label>
            <input type="date" name="ship_date" class="form-control" value="{{ $activity->ship_date?->toDateString() }}">
          </div>
          <div class="col-12">
            <div class="form-check">
              <input type="checkbox" name="active" value="1" class="form-check-input" id="active" @checked($activity->active)>
              <label class="form-check-label" for="active">{{ __('Presale::common.active') }}</label>
            </div>
          </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>{{ __('Presale::common.items') }}</strong>
          <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">+ {{ __('Presale::common.add_item') }}</button>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered align-middle" id="items-table">
            <thead>
            <tr>
              <th>{{ __('Presale::common.sku_id') }}</th>
              <th>{{ __('Presale::common.product_id') }}</th>
              <th>{{ __('Presale::common.presale_price') }}</th>
              <th>{{ __('Presale::common.deposit') }}</th>
              <th>{{ __('Presale::common.expand') }}</th>
              <th>{{ __('Presale::common.qty_limit') }}</th>
              <th></th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-primary">{{ __('Presale::common.saved') }}</button>
          <a href="{{ console_route('presale.index') }}" class="btn btn-light">{{ __('Presale::common.back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    const existing = @json($activity->exists ? $activity->items->map(fn($i) => ['sku_id'=>$i->sku_id,'product_id'=>$i->product_id,'presale_price'=>$i->presale_price,'deposit'=>$i->deposit,'expand'=>$i->expand,'qty_limit'=>$i->qty_limit]) : []);
    const tbody = document.querySelector('#items-table tbody');

    function addRow(item) {
      item = item || { sku_id: '', product_id: '', presale_price: '', deposit: 0, expand: 0, qty_limit: 0 };
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><input type="number" class="form-control form-control-sm i-sku" value="${item.sku_id}"></td>
        <td><input type="number" class="form-control form-control-sm i-pid" value="${item.product_id}"></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm i-price" value="${item.presale_price}"></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm i-deposit" value="${item.deposit}"></td>
        <td><input type="number" step="0.01" class="form-control form-control-sm i-expand" value="${item.expand}"></td>
        <td><input type="number" class="form-control form-control-sm i-qty" value="${item.qty_limit}"></td>
        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger i-del">&times;</button></td>`;
      tr.querySelector('.i-del').addEventListener('click', () => tr.remove());
      tbody.appendChild(tr);
    }

    existing.forEach(addRow);
    if (!existing.length) addRow();
    document.getElementById('btn-add-item').addEventListener('click', () => addRow());

    document.getElementById('presale-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      if (!form.get('active')) form.set('active', '0');
      tbody.querySelectorAll('tr').forEach((tr, idx) => {
        const sku = tr.querySelector('.i-sku').value;
        if (!sku) return;
        form.set(`items[${idx}][sku_id]`, sku);
        form.set(`items[${idx}][product_id]`, tr.querySelector('.i-pid').value || 0);
        form.set(`items[${idx}][presale_price]`, tr.querySelector('.i-price').value || 0);
        form.set(`items[${idx}][deposit]`, tr.querySelector('.i-deposit').value || 0);
        form.set(`items[${idx}][expand]`, tr.querySelector('.i-expand').value || 0);
        form.set(`items[${idx}][qty_limit]`, tr.querySelector('.i-qty').value || 0);
      });
      const isEdit = {{ $activity->exists ? 'true' : 'false' }};
      const url = isEdit ? '{{ $activity->exists ? console_route('presale.update', $activity->id) : '' }}' : '{{ console_route('presale.store') }}';
      if (isEdit) form.append('_method', 'PUT');
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(res => {
        if (res.success) location.href = '{{ console_route('presale.index') }}';
        else alert(res.message || 'Error');
      });
    });
  </script>
@endsection
