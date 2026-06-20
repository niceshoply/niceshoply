@extends('console::layouts.app')

@section('title', __('Bargain::common.menu_title'))

@section('page-title-right')
  <a href="{{ console_route('bargain_activities.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> {{ __('Bargain::common.create') }}
  </a>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Bargain::common.name') }}</th>
            <th>{{ __('Bargain::common.sku_id') }}</th>
            <th>{{ __('Bargain::common.floor_price') }}</th>
            <th>{{ __('Bargain::common.active') }}</th>
            <th class="text-end">{{ __('Bargain::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($activities as $activity)
            <tr>
              <td>{{ $activity->id }}</td>
              <td>{{ $activity->name }}</td>
              <td>{{ $activity->sku_id }}</td>
              <td>{{ currency_format($activity->floor_price) }}</td>
              <td>
                @if($activity->active)<span class="badge bg-success">{{ __('Bargain::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('Bargain::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <a href="{{ console_route('bargain_activities.edit', $activity->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $activity->id }}"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('Bargain::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $activities->links() }}
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm(@json(__('Bargain::common.confirm_delete')))) return;
        fetch('{{ console_route('bargain_activities.index') }}/' + this.dataset.id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
