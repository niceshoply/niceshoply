@extends('console::layouts.app')

@section('title', __('VirtualGoods::common.menu'))

@section('content')
  <div class="mb-3">
    <a href="{{ console_route('virtual_goods.cards') }}" class="btn btn-outline-secondary btn-sm">{{ __('VirtualGoods::common.tab_cards') }}</a>
    <a href="{{ console_route('virtual_goods.deliveries') }}" class="btn btn-outline-secondary btn-sm">{{ __('VirtualGoods::common.tab_deliveries') }}</a>
  </div>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card mb-3">
        <div class="card-header">{{ __('VirtualGoods::common.create') }}</div>
        <div class="card-body">
          <form id="vg-form">
            <div class="mb-2">
              <label class="form-label">{{ __('VirtualGoods::common.product_sku') }}</label>
              <input name="product_sku" id="vg-sku" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">{{ __('VirtualGoods::common.name') }}</label>
              <input name="name" id="vg-name" class="form-control">
            </div>
            <div class="mb-2">
              <label class="form-label">{{ __('VirtualGoods::common.type') }}</label>
              <select name="type" id="vg-type" class="form-select">
                <option value="card">{{ __('VirtualGoods::common.type_card') }}</option>
                <option value="text">{{ __('VirtualGoods::common.type_text') }}</option>
              </select>
            </div>
            <div class="mb-2">
              <label class="form-label">{{ __('VirtualGoods::common.fixed_content') }}</label>
              <textarea name="fixed_content" id="vg-content" class="form-control" rows="3"></textarea>
            </div>
            <input type="hidden" name="is_active" value="1">
            <button type="submit" class="btn btn-primary w-100">{{ __('VirtualGoods::common.submit') }}</button>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">{{ __('VirtualGoods::common.import_title') }}</div>
        <div class="card-body">
          <form id="vg-import">
            <div class="mb-2">
              <label class="form-label">{{ __('VirtualGoods::common.import_sku') }}</label>
              <input name="product_sku" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">{{ __('VirtualGoods::common.import_cards') }}</label>
              <textarea name="cards" class="form-control" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-success w-100">{{ __('VirtualGoods::common.import_btn') }}</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card">
        <div class="card-body table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead>
            <tr>
              <th>SKU</th>
              <th>{{ __('VirtualGoods::common.name') }}</th>
              <th>{{ __('VirtualGoods::common.type') }}</th>
              <th>{{ __('VirtualGoods::common.unused') }}</th>
              <th>{{ __('VirtualGoods::common.is_active') }}</th>
              <th class="text-end">{{ __('VirtualGoods::common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($goods as $g)
              <tr>
                <td>{{ $g->product_sku }}</td>
                <td>{{ $g->name }}</td>
                <td>{{ $g->type === 'text' ? __('VirtualGoods::common.type_text') : __('VirtualGoods::common.type_card') }}</td>
                <td>
                  @if($g->type === 'card')
                    <span class="@if($threshold > 0 && $g->unused <= $threshold) text-danger fw-bold @endif">{{ $g->unused }}</span>
                    @if($threshold > 0 && $g->unused <= $threshold)<span class="badge bg-danger ms-1">{{ __('VirtualGoods::common.low_stock') }}</span>@endif
                  @else
                    -
                  @endif
                </td>
                <td>{!! $g->is_active ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-secondary">OFF</span>' !!}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary btn-edit"
                          data-sku="{{ $g->product_sku }}" data-name="{{ $g->name }}" data-type="{{ $g->type }}"
                          data-content="{{ $g->fixed_content }}">{{ __('VirtualGoods::common.edit') }}</button>
                  <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $g->id }}">{{ __('VirtualGoods::common.delete') }}</button>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted py-4">{{ __('VirtualGoods::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    const base = '{{ console_route('virtual_goods.index') }}';

    document.querySelectorAll('.btn-edit').forEach(function (b) {
      b.addEventListener('click', function () {
        document.getElementById('vg-sku').value = this.dataset.sku;
        document.getElementById('vg-name').value = this.dataset.name;
        document.getElementById('vg-type').value = this.dataset.type;
        document.getElementById('vg-content').value = this.dataset.content;
      });
    });

    document.getElementById('vg-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch(base, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this) })
        .then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });

    document.getElementById('vg-import').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('virtual_goods.import') }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this) })
        .then(r => r.json()).then(res => { alert(res.message); if (res.success) location.reload(); });
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
