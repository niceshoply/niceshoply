@extends('console::layouts.app')

@section('title', __('RiskControl::common.bl_title'))

@section('content')
  <div class="card mb-3"><div class="card-body">
    <form id="bl-form" class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label">{{ __('RiskControl::common.type') }}</label>
        <select name="type" class="form-select">
          <option value="ip">{{ __('RiskControl::common.type_ip') }}</option>
          <option value="email">{{ __('RiskControl::common.type_email') }}</option>
          <option value="phone">{{ __('RiskControl::common.type_phone') }}</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">{{ __('RiskControl::common.value') }}</label>
        <input name="value" class="form-control" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">{{ __('RiskControl::common.reason') }}</label>
        <input name="reason" class="form-control">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">{{ __('RiskControl::common.add') }}</button>
      </div>
    </form>
  </div></div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('RiskControl::common.type') }}</th><th>{{ __('RiskControl::common.value') }}</th>
        <th>{{ __('RiskControl::common.reason') }}</th><th>{{ __('RiskControl::common.created_at') }}</th><th></th>
      </tr></thead>
      <tbody>
      @forelse($list as $b)
        <tr>
          <td>{{ __('RiskControl::common.type_'.$b->type) }}</td>
          <td><code>{{ $b->value }}</code></td>
          <td class="small text-muted">{{ $b->reason }}</td>
          <td>{{ optional($b->created_at)->format('Y-m-d H:i') }}</td>
          <td><button class="btn btn-sm btn-outline-danger del-btn" data-id="{{ $b->id }}">{{ __('RiskControl::common.del') }}</button></td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('RiskControl::common.no_blacklist') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $list->links() }}</div></div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('bl-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('risk_control.blacklist.store') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => { alert(res.message || 'ok'); if (res.success) location.reload(); });
    });
    const delBase = '{{ console_route('risk_control.blacklist.destroy', ['id' => '__ID__']) }}';
    document.querySelectorAll('.del-btn').forEach(btn => btn.addEventListener('click', function () {
      if (!confirm('{{ __('RiskControl::common.confirm_del') }}')) return;
      fetch(delBase.replace('__ID__', this.dataset.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
    }));
  </script>
@endsection
