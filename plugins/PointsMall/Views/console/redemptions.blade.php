@extends('console::layouts.app')

@section('title', __('PointsMall::common.menu_redemptions'))

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>{{ __('PointsMall::common.number') }}</th>
            <th>{{ __('PointsMall::common.customer_id') }}</th>
            <th>{{ __('PointsMall::common.title') }}</th>
            <th>{{ __('PointsMall::common.points_cost') }}</th>
            <th>{{ __('PointsMall::common.quantity') }}</th>
            <th>{{ __('PointsMall::common.contact') }}</th>
            <th>{{ __('PointsMall::common.status') }}</th>
            <th class="text-end">{{ __('PointsMall::common.update_status') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($redemptions as $r)
            <tr>
              <td><code>{{ $r->number }}</code></td>
              <td>{{ $r->customer_id }}</td>
              <td>{{ $r->title }}</td>
              <td>{{ $r->points_cost }}</td>
              <td>{{ $r->quantity }}</td>
              <td class="text-truncate" style="max-width:200px">{{ $r->contact }}</td>
              <td><span class="badge bg-secondary">{{ __('PointsMall::common.st_'.$r->status) }}</span></td>
              <td class="text-end">
                <select class="form-select form-select-sm d-inline-block w-auto sel-status" data-id="{{ $r->id }}">
                  @foreach(['pending','shipped','completed','cancelled'] as $st)
                    <option value="{{ $st }}" @selected($r->status === $st)>{{ __('PointsMall::common.st_'.$st) }}</option>
                  @endforeach
                </select>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">{{ __('PointsMall::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $redemptions->links() }}</div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.sel-status').forEach(function (sel) {
      sel.addEventListener('change', function () {
        const form = new FormData();
        form.set('status', this.value);
        form.append('_method', 'PUT');
        fetch('{{ console_route('points_mall.redemptions') }}/' + this.dataset.id, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
