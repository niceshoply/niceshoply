@extends('console::layouts.app')

@section('title', __('Wholesale::common.menu'))

@section('content')
  <div class="alert alert-info">{{ __('Wholesale::common.tip') }}</div>

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">{{ __('Wholesale::common.create') }}</div>
        <div class="card-body">
          <form id="w-form">
            <div class="mb-2">
              <label class="form-label">{{ __('Wholesale::common.product_sku') }}</label>
              <input name="product_sku" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">{{ __('Wholesale::common.min_qty') }}</label>
              <input name="min_qty" type="number" min="1" class="form-control" value="2" required>
            </div>
            <div class="mb-2">
              <label class="form-label">{{ __('Wholesale::common.price') }}</label>
              <input name="price" type="number" step="0.01" min="0" class="form-control" required>
            </div>
            <input type="hidden" name="is_active" value="1">
            <button type="submit" class="btn btn-primary w-100">{{ __('Wholesale::common.submit') }}</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead>
            <tr>
              <th>SKU ID</th>
              <th>SKU</th>
              <th>{{ __('Wholesale::common.min_qty') }}</th>
              <th>{{ __('Wholesale::common.price') }}</th>
              <th>{{ __('Wholesale::common.is_active') }}</th>
              <th class="text-end">{{ __('Wholesale::common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($tiers as $t)
              <tr>
                <td>{{ $t->sku_id }}</td>
                <td>{{ $t->product_sku }}</td>
                <td>≥ {{ $t->min_qty }}</td>
                <td>{{ currency_format($t->price) }}</td>
                <td>{!! $t->is_active ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-secondary">OFF</span>' !!}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $t->id }}">{{ __('Wholesale::common.delete') }}</button>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted py-4">{{ __('Wholesale::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
        <div class="card-footer">{{ $tiers->links() }}</div>
      </div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    const base = '{{ console_route('wholesale.index') }}';

    document.getElementById('w-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch(base, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this) })
        .then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });

    document.querySelectorAll('.btn-del').forEach(function (b) {
      b.addEventListener('click', function () {
        if (!confirm('?')) return;
        const fd = new FormData(); fd.append('_method', 'DELETE');
        fetch(base + '/' + this.dataset.id, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd })
          .then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
