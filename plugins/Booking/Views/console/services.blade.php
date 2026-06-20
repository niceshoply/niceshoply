@extends('console::layouts.app')

@section('title', __('Booking::common.menu_services'))

@section('content')
  <div class="mb-3">
    <a href="{{ console_route('booking.bookings') }}" class="btn btn-outline-secondary btn-sm">{{ __('Booking::common.menu_bookings') }}</a>
  </div>

  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">{{ __('Booking::common.create_service') }}</div>
        <div class="card-body">
          <form id="svc-form">
            <input type="hidden" name="id" id="svc-id">
            <div class="mb-2"><label class="form-label">{{ __('Booking::common.name') }}</label><input name="name" id="svc-name" class="form-control" required></div>
            <div class="mb-2"><label class="form-label">{{ __('Booking::common.product_sku') }}</label><input name="product_sku" id="svc-sku" class="form-control"></div>
            <div class="row g-2">
              <div class="col-6 mb-2"><label class="form-label">{{ __('Booking::common.price') }}</label><input name="price" id="svc-price" type="number" step="0.01" class="form-control" value="0"></div>
              <div class="col-6 mb-2"><label class="form-label">{{ __('Booking::common.capacity') }}</label><input name="capacity" id="svc-cap" type="number" min="1" class="form-control" value="1" required></div>
              <div class="col-6 mb-2"><label class="form-label">{{ __('Booking::common.duration_min') }}</label><input name="duration_min" id="svc-dur" type="number" min="5" class="form-control" value="60" required></div>
              <div class="col-6 mb-2"><label class="form-label">{{ __('Booking::common.slot_interval_min') }}</label><input name="slot_interval_min" id="svc-step" type="number" min="5" class="form-control" value="60" required></div>
              <div class="col-6 mb-2"><label class="form-label">{{ __('Booking::common.open_time') }}</label><input name="open_time" id="svc-open" class="form-control" value="09:00" required></div>
              <div class="col-6 mb-2"><label class="form-label">{{ __('Booking::common.close_time') }}</label><input name="close_time" id="svc-close" class="form-control" value="18:00" required></div>
            </div>
            <div class="mb-2"><label class="form-label">{{ __('Booking::common.open_weekdays') }}</label><input name="open_weekdays" id="svc-week" class="form-control" value="1,2,3,4,5,6,7"></div>
            <input type="hidden" name="is_active" value="1">
            <button type="submit" class="btn btn-primary w-100">{{ __('Booking::common.submit') }}</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card"><div class="card-body table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead><tr>
            <th>#</th><th>{{ __('Booking::common.name') }}</th><th>{{ __('Booking::common.capacity') }}</th>
            <th>{{ __('Booking::common.open_time') }}</th><th>{{ __('Booking::common.is_active') }}</th>
            <th class="text-end">{{ __('Booking::common.actions') }}</th>
          </tr></thead>
          <tbody>
          @forelse($services as $s)
            <tr>
              <td>{{ $s->id }}</td>
              <td>{{ $s->name }}<div class="text-muted small">{{ $s->product_sku }}</div></td>
              <td>{{ $s->capacity }}</td>
              <td>{{ $s->open_time }}-{{ $s->close_time }}<div class="text-muted small">{{ $s->open_weekdays }}</div></td>
              <td>{!! $s->is_active ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-secondary">OFF</span>' !!}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary btn-edit"
                  data-json='@json($s)'>{{ __('Booking::common.edit') }}</button>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $s->id }}">{{ __('Booking::common.delete') }}</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('Booking::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div></div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    const base = '{{ console_route('booking.services') }}';

    document.querySelectorAll('.btn-edit').forEach(function (b) {
      b.addEventListener('click', function () {
        const d = JSON.parse(this.dataset.json);
        svcId.value = d.id; svcName.value = d.name; svcSku.value = d.product_sku || '';
        svcPrice.value = d.price; svcCap.value = d.capacity; svcDur.value = d.duration_min;
        svcStep.value = d.slot_interval_min; svcOpen.value = d.open_time; svcClose.value = d.close_time;
        svcWeek.value = d.open_weekdays;
      });
    });

    const svcId=document.getElementById('svc-id'),svcName=document.getElementById('svc-name'),svcSku=document.getElementById('svc-sku'),
      svcPrice=document.getElementById('svc-price'),svcCap=document.getElementById('svc-cap'),svcDur=document.getElementById('svc-dur'),
      svcStep=document.getElementById('svc-step'),svcOpen=document.getElementById('svc-open'),svcClose=document.getElementById('svc-close'),
      svcWeek=document.getElementById('svc-week');

    document.getElementById('svc-form').addEventListener('submit', function (e) {
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
