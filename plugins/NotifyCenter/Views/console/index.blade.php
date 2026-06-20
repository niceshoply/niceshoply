@extends('console::layouts.app')

@section('title', __('NotifyCenter::common.menu_title'))

@section('content')
  <div class="card mb-3">
    <div class="card-body">
      <form id="send-form" class="row g-2 align-items-end">
        <div class="col-md-2">
          <label class="form-label">{{ __('NotifyCenter::common.customer_id') }}</label>
          <input type="number" name="customer_id" class="form-control" value="0">
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('NotifyCenter::common.title') }} *</label>
          <input type="text" name="title" class="form-control" required>
        </div>
        <div class="col-md-5">
          <label class="form-label">{{ __('NotifyCenter::common.content') }}</label>
          <input type="text" name="content" class="form-control">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">{{ __('NotifyCenter::common.send') }}</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('NotifyCenter::common.customer_id') }}</th>
            <th>{{ __('NotifyCenter::common.title') }}</th>
            <th>{{ __('NotifyCenter::common.type') }}</th>
            <th>{{ __('NotifyCenter::common.status') }}</th>
            <th>{{ __('NotifyCenter::common.created_at') }}</th>
            <th class="text-end">{{ __('NotifyCenter::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($notifications as $n)
            <tr>
              <td>{{ $n->id }}</td>
              <td>{{ $n->customer_id == 0 ? __('NotifyCenter::common.broadcast') : $n->customer_id }}</td>
              <td>{{ $n->title }}</td>
              <td><span class="badge bg-light text-dark">{{ $n->type }}</span></td>
              <td>
                @if($n->read_at)<span class="badge bg-secondary">{{ __('NotifyCenter::common.read') }}</span>
                @else<span class="badge bg-warning text-dark">{{ __('NotifyCenter::common.unread') }}</span>@endif
              </td>
              <td>{{ $n->created_at?->format('Y-m-d H:i') }}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $n->id }}"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('NotifyCenter::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $notifications->links() }}
    </div>
  </div>

  <script>
    document.getElementById('send-form').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('{{ console_route('notifications.send') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: new FormData(this)
      }).then(r => r.json()).then(() => location.reload());
    });
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function () {
        fetch('{{ console_route('notifications.index') }}/' + this.dataset.id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
