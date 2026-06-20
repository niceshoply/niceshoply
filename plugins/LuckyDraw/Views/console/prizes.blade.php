@extends('console::layouts.app')

@section('title', __('LuckyDraw::common.prizes_title'))

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted small mb-0">{{ __('LuckyDraw::common.tip') }}</p>
    <a href="{{ console_route('lucky_draw.records') }}" class="btn btn-sm btn-outline-secondary">{{ __('LuckyDraw::common.view_records') }}</a>
  </div>

  <div class="card mb-3"><div class="card-body">
    <form id="prize-form" class="row g-2">
      <input type="hidden" name="id" id="p-id">
      <div class="col-md-2"><input name="name" id="p-name" class="form-control" placeholder="{{ __('LuckyDraw::common.name') }}" required></div>
      <div class="col-md-2">
        <select name="type" id="p-type" class="form-select">
          <option value="thanks">thanks</option>
          <option value="points">points</option>
          <option value="coupon">coupon</option>
        </select>
      </div>
      <div class="col-md-2"><input name="value" id="p-value" class="form-control" placeholder="{{ __('LuckyDraw::common.value') }}"></div>
      <div class="col-md-1"><input name="weight" id="p-weight" type="number" min="0" class="form-control" placeholder="{{ __('LuckyDraw::common.weight') }}" value="1" required></div>
      <div class="col-md-1"><input name="stock" id="p-stock" type="number" class="form-control" placeholder="{{ __('LuckyDraw::common.stock') }}" value="-1" required></div>
      <div class="col-md-1"><input name="sort" id="p-sort" type="number" class="form-control" placeholder="{{ __('LuckyDraw::common.sort') }}" value="0"></div>
      <div class="col-md-2">
        <select name="is_active" id="p-active" class="form-select">
          <option value="1">{{ __('LuckyDraw::common.active') }}: ✓</option>
          <option value="0">{{ __('LuckyDraw::common.active') }}: ✗</option>
        </select>
      </div>
      <div class="col-md-1"><button class="btn btn-primary w-100">{{ __('LuckyDraw::common.save') }}</button></div>
    </form>
  </div></div>

  <div class="card mb-3"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('LuckyDraw::common.sort') }}</th><th>{{ __('LuckyDraw::common.name') }}</th><th>{{ __('LuckyDraw::common.type') }}</th>
        <th>{{ __('LuckyDraw::common.value') }}</th><th>{{ __('LuckyDraw::common.weight') }}</th><th>{{ __('LuckyDraw::common.stock') }}</th>
        <th>{{ __('LuckyDraw::common.active') }}</th><th></th>
      </tr></thead>
      <tbody>
      @forelse($prizes as $p)
        <tr>
          <td>{{ $p->sort }}</td>
          <td>{{ $p->name }}</td>
          <td>{{ $p->type }}</td>
          <td>{{ $p->value }}</td>
          <td>{{ $p->weight }}</td>
          <td>{{ $p->stock }}</td>
          <td>@if($p->is_active)<span class="badge bg-success">✓</span>@else<span class="badge bg-secondary">✗</span>@endif</td>
          <td class="text-end text-nowrap">
            <button class="btn btn-sm btn-outline-secondary edit-btn"
              data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-type="{{ $p->type }}" data-value="{{ $p->value }}"
              data-weight="{{ $p->weight }}" data-stock="{{ $p->stock }}" data-sort="{{ $p->sort }}"
              data-active="{{ (int)$p->is_active }}">{{ __('LuckyDraw::common.edit') }}</button>
            <button class="btn btn-sm btn-outline-danger del-btn" data-id="{{ $p->id }}">{{ __('LuckyDraw::common.del') }}</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center text-muted py-4">{{ __('LuckyDraw::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div></div>

  <div class="card"><div class="card-header">{{ __('LuckyDraw::common.api_title') }}</div>
    <div class="card-body small">
      <pre class="bg-light p-2 mb-0" style="white-space:pre-wrap">GET  /lucky-draw/info
POST /lucky-draw/draw</pre>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('prize-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('lucky_draw.prizes.store') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
    });
    document.querySelectorAll('.edit-btn').forEach(b => b.addEventListener('click', function () {
      const d = this.dataset;
      document.getElementById('p-id').value = d.id;
      document.getElementById('p-name').value = d.name;
      document.getElementById('p-type').value = d.type;
      document.getElementById('p-value').value = d.value;
      document.getElementById('p-weight').value = d.weight;
      document.getElementById('p-stock').value = d.stock;
      document.getElementById('p-sort').value = d.sort;
      document.getElementById('p-active').value = d.active;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }));
    const delBase = '{{ console_route('lucky_draw.prizes.destroy', ['id' => '__ID__']) }}';
    document.querySelectorAll('.del-btn').forEach(b => b.addEventListener('click', function () {
      if (!confirm('{{ __('LuckyDraw::common.confirm_del') }}')) return;
      fetch(delBase.replace('__ID__', this.dataset.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
    });
  </script>
@endsection
