@extends('console::layouts.app')

@section('title', __('Invoice::common.menu_invoices'))

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>{{ __('Invoice::common.number') }}</th>
            <th>{{ __('Invoice::common.order_id') }}</th>
            <th>{{ __('Invoice::common.title_type') }}</th>
            <th>{{ __('Invoice::common.title') }}</th>
            <th>{{ __('Invoice::common.tax_no') }}</th>
            <th>{{ __('Invoice::common.amount') }}</th>
            <th>{{ __('Invoice::common.status') }}</th>
            <th class="text-end">{{ __('Invoice::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($invoices as $inv)
            <tr>
              <td><code>{{ $inv->number }}</code></td>
              <td>{{ $inv->order_id }}</td>
              <td>{{ __('Invoice::common.type_'.$inv->title_type) }}</td>
              <td>{{ $inv->title }}</td>
              <td>{{ $inv->tax_no }}</td>
              <td>{{ currency_format($inv->amount) }}</td>
              <td>
                @switch($inv->status)
                  @case('issued')<span class="badge bg-success">{{ __('Invoice::common.status_issued') }}</span>@break
                  @case('rejected')<span class="badge bg-danger">{{ __('Invoice::common.status_rejected') }}</span>@break
                  @default<span class="badge bg-warning text-dark">{{ __('Invoice::common.status_pending') }}</span>
                @endswitch
                @if($inv->invoice_no)<div class="small text-muted mt-1">{{ $inv->invoice_no }}</div>@endif
              </td>
              <td class="text-end">
                @if($inv->status === 'pending')
                  <button class="btn btn-sm btn-outline-success btn-issue" data-id="{{ $inv->id }}">{{ __('Invoice::common.issue') }}</button>
                  <button class="btn btn-sm btn-outline-danger btn-reject" data-id="{{ $inv->id }}">{{ __('Invoice::common.reject') }}</button>
                @elseif($inv->invoice_file)
                  <a class="btn btn-sm btn-outline-primary" href="{{ $inv->invoice_file }}" target="_blank">{{ __('Invoice::common.invoice_file') }}</a>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">{{ __('Invoice::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $invoices->links() }}
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.querySelectorAll('.btn-issue').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const no = prompt('{{ __('Invoice::common.invoice_no') }}');
        if (!no) return;
        const file = prompt('{{ __('Invoice::common.invoice_file') }} (URL)') || '';
        const form = new FormData();
        form.set('invoice_no', no);
        form.set('invoice_file', file);
        fetch('{{ console_route('invoices.index') }}/' + this.dataset.id + '/issue', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
    document.querySelectorAll('.btn-reject').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const remark = prompt('{{ __('Invoice::common.admin_remark') }}') || '';
        const form = new FormData();
        form.set('admin_remark', remark);
        fetch('{{ console_route('invoices.index') }}/' + this.dataset.id + '/reject', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
