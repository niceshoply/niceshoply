@extends('console::layouts.app')

@section('title', __('BuyXGetY::common.title'))

@section('content')
  <div class="card mb-3"><div class="card-body">
    <p class="text-muted small">{{ __('BuyXGetY::common.tip') }}</p>
    <form id="rule-form" class="row g-2">
      <input type="hidden" name="id" id="r-id">
      <div class="col-md-3"><input name="name" id="r-name" class="form-control" placeholder="{{ __('BuyXGetY::common.name') }}" required></div>
      <div class="col-md-2"><input name="product_id" id="r-pid" type="number" min="0" class="form-control" placeholder="{{ __('BuyXGetY::common.product_id') }}" value="0"></div>
      <div class="col-md-1"><input name="buy_qty" id="r-buy" type="number" min="1" class="form-control" placeholder="X" value="1" required></div>
      <div class="col-md-1"><input name="get_qty" id="r-get" type="number" min="1" class="form-control" placeholder="Y" value="1" required></div>
      <div class="col-md-2"><input name="discount_percent" id="r-pct" type="number" min="1" max="100" class="form-control" placeholder="{{ __('BuyXGetY::common.discount') }}" value="50" required></div>
      <div class="col-md-2">
        <select name="is_active" id="r-active" class="form-select">
          <option value="1">{{ __('BuyXGetY::common.active') }}: ✓</option>
          <option value="0">{{ __('BuyXGetY::common.active') }}: ✗</option>
        </select>
      </div>
      <div class="col-md-1"><button class="btn btn-primary w-100">{{ __('BuyXGetY::common.save') }}</button></div>
    </form>
  </div></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('BuyXGetY::common.name') }}</th><th>{{ __('BuyXGetY::common.product_id') }}</th>
        <th>{{ __('BuyXGetY::common.buy_qty') }}</th><th>{{ __('BuyXGetY::common.get_qty') }}</th>
        <th>{{ __('BuyXGetY::common.discount') }}</th><th>{{ __('BuyXGetY::common.active') }}</th><th></th>
      </tr></thead>
      <tbody>
      @forelse($rules as $r)
        <tr>
          <td>{{ $r->name }}</td>
          <td>{{ $r->product_id ?: __('BuyXGetY::common.all') }}</td>
          <td>{{ $r->buy_qty }}</td>
          <td>{{ $r->get_qty }}</td>
          <td>{{ $r->discount_percent }}%</td>
          <td>@if($r->is_active)<span class="badge bg-success">✓</span>@else<span class="badge bg-secondary">✗</span>@endif</td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-secondary edit-btn"
              data-id="{{ $r->id }}" data-name="{{ $r->name }}" data-pid="{{ $r->product_id }}"
              data-buy="{{ $r->buy_qty }}" data-get="{{ $r->get_qty }}" data-pct="{{ $r->discount_percent }}"
              data-active="{{ (int)$r->is_active }}">{{ __('BuyXGetY::common.edit') }}</button>
            <button class="btn btn-sm btn-outline-danger del-btn" data-id="{{ $r->id }}">{{ __('BuyXGetY::common.del') }}</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('BuyXGetY::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div></div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('rule-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('buy_x_get_y.store') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });
    document.querySelectorAll('.edit-btn').forEach(b => b.addEventListener('click', function () {
      const d = this.dataset;
      document.getElementById('r-id').value = d.id;
      document.getElementById('r-name').value = d.name;
      document.getElementById('r-pid').value = d.pid;
      document.getElementById('r-buy').value = d.buy;
      document.getElementById('r-get').value = d.get;
      document.getElementById('r-pct').value = d.pct;
      document.getElementById('r-active').value = d.active;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }));
    const delBase = '{{ console_route('buy_x_get_y.destroy', ['id' => '__ID__']) }}';
    document.querySelectorAll('.del-btn').forEach(b => b.addEventListener('click', function () {
      if (!confirm('{{ __('BuyXGetY::common.confirm_del') }}')) return;
      fetch(delBase.replace('__ID__', this.dataset.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
    }));
  </script>
@endsection
