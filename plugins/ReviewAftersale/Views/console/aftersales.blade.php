@extends('console::layouts.app')

@section('title', __('ReviewAftersale::common.menu_aftersales'))

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>{{ __('ReviewAftersale::common.number') }}</th>
            <th>{{ __('ReviewAftersale::common.order_id') }}</th>
            <th>{{ __('ReviewAftersale::common.customer_id') }}</th>
            <th>{{ __('ReviewAftersale::common.type') }}</th>
            <th>{{ __('ReviewAftersale::common.reason') }}</th>
            <th>{{ __('ReviewAftersale::common.refund_amount') }}</th>
            <th>{{ __('ReviewAftersale::common.status') }}</th>
            <th class="text-end">{{ __('ReviewAftersale::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($requests as $req)
            <tr>
              <td><code>{{ $req->number }}</code></td>
              <td>{{ $req->order_id }}</td>
              <td>{{ $req->customer_id }}</td>
              <td>{{ __('ReviewAftersale::common.type_'.$req->type) }}</td>
              <td class="text-truncate" style="max-width:220px">{{ $req->reason }}</td>
              <td>{{ currency_format($req->refund_amount) }}</td>
              <td><span class="badge bg-secondary">{{ __('ReviewAftersale::common.as_'.$req->status) }}</span></td>
              <td class="text-end">
                <select class="form-select form-select-sm d-inline-block w-auto sel-status" data-id="{{ $req->id }}">
                  @foreach(['pending','approved','rejected','processing','completed'] as $st)
                    <option value="{{ $st }}" @selected($req->status === $st)>{{ __('ReviewAftersale::common.as_'.$st) }}</option>
                  @endforeach
                </select>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">{{ __('ReviewAftersale::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $requests->links() }}
    </div>
  </div>

  <script>
    document.querySelectorAll('.sel-status').forEach(function (sel) {
      sel.addEventListener('change', function () {
        const form = new FormData();
        form.set('status', this.value);
        form.append('_method', 'PUT');
        fetch('{{ console_route('aftersales.index') }}/' + this.dataset.id, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
