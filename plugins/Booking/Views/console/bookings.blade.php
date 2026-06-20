@extends('console::layouts.app')

@section('title', __('Booking::common.menu_bookings'))

@section('content')
  <div class="mb-3 d-flex justify-content-between">
    <div>
      <a href="?" class="btn btn-sm {{ $status === '' ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('Booking::common.all') }}</a>
      @foreach(['pending','confirmed','completed','cancelled'] as $st)
        <a href="?status={{ $st }}" class="btn btn-sm {{ $status === $st ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('Booking::common.status_'.$st) }}</a>
      @endforeach
    </div>
    <a href="{{ console_route('booking.services') }}" class="btn btn-outline-secondary btn-sm">{{ __('Booking::common.menu_services') }}</a>
  </div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>#</th><th>{{ __('Booking::common.service') }}</th><th>{{ __('Booking::common.customer_name') }}</th>
        <th>{{ __('Booking::common.date') }}</th><th>{{ __('Booking::common.time') }}</th><th>{{ __('Booking::common.people') }}</th>
        <th>{{ __('Booking::common.status') }}</th><th class="text-end">{{ __('Booking::common.actions') }}</th>
      </tr></thead>
      <tbody>
      @forelse($bookings as $b)
        <tr>
          <td>{{ $b->id }}</td>
          <td>{{ optional($b->service)->name }}</td>
          <td>{{ $b->customer_name }}<div class="text-muted small">{{ $b->phone }} / #{{ $b->customer_id }}</div></td>
          <td>{{ optional($b->booking_date)->format('Y-m-d') }}</td>
          <td>{{ $b->booking_time }}</td>
          <td>{{ $b->people }}</td>
          <td>
            @php($map = ['pending'=>'bg-warning text-dark','confirmed'=>'bg-info','completed'=>'bg-success','cancelled'=>'bg-secondary'])
            <span class="badge {{ $map[$b->status] ?? 'bg-secondary' }}">{{ __('Booking::common.status_'.$b->status) }}</span>
          </td>
          <td class="text-end">
            @if($b->status !== 'cancelled' && $b->status !== 'completed')
              <button class="btn btn-sm btn-outline-info bk-status" data-id="{{ $b->id }}" data-status="confirmed">{{ __('Booking::common.confirm') }}</button>
              <button class="btn btn-sm btn-outline-success bk-status" data-id="{{ $b->id }}" data-status="completed">{{ __('Booking::common.complete') }}</button>
              <button class="btn btn-sm btn-outline-secondary bk-status" data-id="{{ $b->id }}" data-status="cancelled">{{ __('Booking::common.cancel') }}</button>
            @endif
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center text-muted py-4">{{ __('Booking::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $bookings->appends(['status' => $status])->links() }}</div></div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.querySelectorAll('.bk-status').forEach(function (b) {
      b.addEventListener('click', function () {
        const fd = new FormData(); fd.append('_method', 'PUT'); fd.append('status', this.dataset.status);
        fetch('{{ console_route('booking.bookings') }}/' + this.dataset.id + '/status', {
          method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd
        }).then(r => r.json()).then(res => { if (res.success) location.reload(); else alert(res.message); });
      });
    });
  </script>
@endsection
