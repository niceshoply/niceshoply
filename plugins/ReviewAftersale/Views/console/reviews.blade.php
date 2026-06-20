@extends('console::layouts.app')

@section('title', __('ReviewAftersale::common.menu_reviews'))

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('ReviewAftersale::common.product_id') }}</th>
            <th>{{ __('ReviewAftersale::common.customer_id') }}</th>
            <th>{{ __('ReviewAftersale::common.rating') }}</th>
            <th>{{ __('ReviewAftersale::common.content') }}</th>
            <th>{{ __('ReviewAftersale::common.status') }}</th>
            <th class="text-end">{{ __('ReviewAftersale::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($reviews as $r)
            <tr>
              <td>{{ $r->id }}</td>
              <td>{{ $r->product_id }}</td>
              <td>{{ $r->customer_id }}</td>
              <td>{{ str_repeat('★', (int) $r->rating) }}{{ str_repeat('☆', 5 - (int) $r->rating) }}</td>
              <td class="text-truncate" style="max-width:280px">{{ $r->content }}</td>
              <td>
                @switch($r->status)
                  @case('approved')<span class="badge bg-success">{{ __('ReviewAftersale::common.status_approved') }}</span>@break
                  @case('rejected')<span class="badge bg-danger">{{ __('ReviewAftersale::common.status_rejected') }}</span>@break
                  @default<span class="badge bg-warning text-dark">{{ __('ReviewAftersale::common.status_pending') }}</span>
                @endswitch
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-success btn-audit" data-id="{{ $r->id }}" data-status="approved">{{ __('ReviewAftersale::common.approve') }}</button>
                <button class="btn btn-sm btn-outline-danger btn-audit" data-id="{{ $r->id }}" data-status="rejected">{{ __('ReviewAftersale::common.reject') }}</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('ReviewAftersale::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $reviews->links() }}
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-audit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const form = new FormData();
        form.set('status', this.dataset.status);
        fetch('{{ console_route('reviews.index') }}/' + this.dataset.id + '/audit', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
