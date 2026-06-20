@extends('console::layouts.app')

@section('title', __('StorePickup::common.menu'))

@section('content')
  <div class="card mb-3">
    <div class="card-body">
      <form id="store-form" class="row g-2 align-items-end">
        <input type="hidden" name="id" id="store-id">
        <div class="col-md-3">
          <label class="form-label">{{ __('StorePickup::common.name') }}</label>
          <input name="name" id="store-name" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('StorePickup::common.phone') }}</label>
          <input name="phone" id="store-phone" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('StorePickup::common.address') }}</label>
          <input name="address" id="store-address" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('StorePickup::common.business_hours') }}</label>
          <input name="business_hours" id="store-hours" class="form-control" placeholder="09:00-21:00">
        </div>
        <div class="col-md-1">
          <label class="form-label">{{ __('StorePickup::common.sort') }}</label>
          <input name="sort" id="store-sort" type="number" class="form-control" value="0">
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary w-100">{{ __('StorePickup::common.submit') }}</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('StorePickup::common.name') }}</th>
            <th>{{ __('StorePickup::common.phone') }}</th>
            <th>{{ __('StorePickup::common.address') }}</th>
            <th>{{ __('StorePickup::common.business_hours') }}</th>
            <th>{{ __('StorePickup::common.is_active') }}</th>
            <th class="text-end">{{ __('StorePickup::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($stores as $s)
            <tr>
              <td>{{ $s->id }}</td>
              <td>{{ $s->name }}</td>
              <td>{{ $s->phone }}</td>
              <td>{{ $s->address }}</td>
              <td>{{ $s->business_hours }}</td>
              <td>
                @if($s->is_active)<span class="badge bg-success">{{ __('StorePickup::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('StorePickup::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="{{ $s->id }}" data-name="{{ $s->name }}" data-phone="{{ $s->phone }}"
                        data-address="{{ $s->address }}" data-hours="{{ $s->business_hours }}" data-sort="{{ $s->sort }}">{{ __('StorePickup::common.edit') }}</button>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $s->id }}">{{ __('StorePickup::common.delete') }}</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('StorePickup::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $stores->links() }}</div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    const base = '{{ console_route('store_pickup.index') }}';

    document.querySelectorAll('.btn-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('store-id').value = this.dataset.id;
        document.getElementById('store-name').value = this.dataset.name;
        document.getElementById('store-phone').value = this.dataset.phone;
        document.getElementById('store-address').value = this.dataset.address;
        document.getElementById('store-hours').value = this.dataset.hours;
        document.getElementById('store-sort').value = this.dataset.sort;
      });
    });

    document.getElementById('store-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const id = document.getElementById('store-id').value;
      const form = new FormData(this);
      form.set('is_active', '1');
      let url = base;
      if (id) { url = base + '/' + id; form.append('_method', 'PUT'); }
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(function (res) {
        if (res.success) location.reload(); else alert(res.message || 'error');
      });
    });

    document.querySelectorAll('.btn-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm('?')) return;
        const form = new FormData();
        form.append('_method', 'DELETE');
        fetch(base + '/' + this.dataset.id, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
