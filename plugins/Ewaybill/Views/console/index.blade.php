@extends('console::layouts.app')

@section('title', __('Ewaybill::common.menu'))

@section('content')
  <div class="card mb-3">
    <div class="card-body">
      <p class="text-muted">{{ __('Ewaybill::common.tip') }}</p>
      <form id="wb-form" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">{{ __('Ewaybill::common.order_id') }}</label>
          <input name="order_id" type="number" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('Ewaybill::common.shipper_code') }}</label>
          <input name="shipper_code" class="form-control" placeholder="SF / YTO / ZTO" required>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">{{ __('Ewaybill::common.submit') }}</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>{{ __('Ewaybill::common.id') }}</th><th>{{ __('Ewaybill::common.order') }}</th><th>{{ __('Ewaybill::common.shipper') }}</th>
        <th>{{ __('Ewaybill::common.logistic_code') }}</th><th>{{ __('Ewaybill::common.status') }}</th>
        <th>{{ __('Ewaybill::common.message') }}</th><th>{{ __('Ewaybill::common.created_at') }}</th>
      </tr></thead>
      <tbody>
      @forelse($waybills as $w)
        <tr>
          <td>{{ $w->id }}</td>
          <td>{{ $w->order_number }}</td>
          <td>{{ $w->shipper_code }}</td>
          <td><code>{{ $w->logistic_code }}</code></td>
          <td>
            @if($w->status === 'success')<span class="badge bg-success">{{ __('Ewaybill::common.status_success') }}</span>
            @else<span class="badge bg-danger">{{ __('Ewaybill::common.status_failed') }}</span>@endif
          </td>
          <td class="small text-muted text-truncate" style="max-width:240px">{{ $w->message }}</td>
          <td>{{ optional($w->created_at)->format('Y-m-d H:i') }}</td>
        </tr>
      @empty
        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('Ewaybill::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $waybills->links() }}</div></div>

  <script>
    document.getElementById('wb-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = this.querySelector('button'); btn.disabled = true;
      fetch('{{ console_route('ewaybill.create') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(function (res) {
        btn.disabled = false; alert(res.message || 'done');
        if (res.success) location.reload();
      }).catch(() => { btn.disabled = false; });
    });
  </script>
@endsection
