@extends('console::layouts.app')

@section('title', __('Bundle::common.title'))

@section('content')
  <div class="card mb-3"><div class="card-body">
    <p class="text-muted small">{{ __('Bundle::common.tip') }}</p>
    <form id="bundle-form" class="row g-2">
      <input type="hidden" name="id" id="b-id">
      <div class="col-md-3"><input name="name" id="b-name" class="form-control" placeholder="{{ __('Bundle::common.name') }}" required></div>
      <div class="col-md-4"><input name="items" id="b-items" class="form-control" placeholder="{{ __('Bundle::common.items') }}：12:1, 15:2" required></div>
      <div class="col-md-2"><input name="bundle_price" id="b-price" type="number" step="0.01" min="0" class="form-control" placeholder="{{ __('Bundle::common.bundle_price') }}" required></div>
      <div class="col-md-2">
        <select name="is_active" id="b-active" class="form-select">
          <option value="1">{{ __('Bundle::common.active') }}: ✓</option>
          <option value="0">{{ __('Bundle::common.active') }}: ✗</option>
        </select>
      </div>
      <div class="col-md-1"><button class="btn btn-primary w-100">{{ __('Bundle::common.save') }}</button></div>
    </form>
  </div></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('Bundle::common.name') }}</th><th>{{ __('Bundle::common.items') }}</th>
        <th>{{ __('Bundle::common.bundle_price') }}</th><th>{{ __('Bundle::common.active') }}</th><th></th>
      </tr></thead>
      <tbody>
      @forelse($deals as $d)
        @php $itemsStr = collect($d->items)->map(fn($i) => $i['product_id'].':'.$i['quantity'])->implode(', '); @endphp
        <tr>
          <td>{{ $d->name }}</td>
          <td class="small">{{ $itemsStr }}</td>
          <td>{{ currency_format($d->bundle_price) }}</td>
          <td>@if($d->is_active)<span class="badge bg-success">✓</span>@else<span class="badge bg-secondary">✗</span>@endif</td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-secondary edit-btn"
              data-id="{{ $d->id }}" data-name="{{ $d->name }}" data-items="{{ $itemsStr }}"
              data-price="{{ $d->bundle_price }}" data-active="{{ (int)$d->is_active }}">{{ __('Bundle::common.edit') }}</button>
            <button class="btn btn-sm btn-outline-danger del-btn" data-id="{{ $d->id }}">{{ __('Bundle::common.del') }}</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('Bundle::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div></div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('bundle-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('bundle.store') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });
    document.querySelectorAll('.edit-btn').forEach(b => b.addEventListener('click', function () {
      const d = this.dataset;
      document.getElementById('b-id').value = d.id;
      document.getElementById('b-name').value = d.name;
      document.getElementById('b-items').value = d.items;
      document.getElementById('b-price').value = d.price;
      document.getElementById('b-active').value = d.active;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }));
    const delBase = '{{ console_route('bundle.destroy', ['id' => '__ID__']) }}';
    document.querySelectorAll('.del-btn').forEach(b => b.addEventListener('click', function () {
      if (!confirm('{{ __('Bundle::common.confirm_del') }}')) return;
      fetch(delBase.replace('__ID__', this.dataset.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
    }));
  </script>
@endsection
