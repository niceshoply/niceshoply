@extends('console::layouts.app')

@section('title', __('GroupBuy::common.menu_title'))

@section('page-title-right')
  <a href="{{ console_route('group_buy_activities.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> {{ __('GroupBuy::common.create') }}
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
            <th>{{ __('GroupBuy::common.name') }}</th>
            <th>{{ __('GroupBuy::common.sku_id') }}</th>
            <th>{{ __('GroupBuy::common.group_price') }}</th>
            <th>{{ __('GroupBuy::common.group_size') }}</th>
            <th>{{ __('GroupBuy::common.active') }}</th>
            <th class="text-end">{{ __('GroupBuy::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($activities as $activity)
            <tr>
              <td>{{ $activity->id }}</td>
              <td>{{ $activity->name }}</td>
              <td>{{ $activity->sku_id }}</td>
              <td>{{ currency_format($activity->group_price) }}</td>
              <td>{{ $activity->group_size }}</td>
              <td>
                @if($activity->active)<span class="badge bg-success">{{ __('GroupBuy::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('GroupBuy::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <a href="{{ console_route('group_buy_activities.groups', $activity->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-people"></i></a>
                <a href="{{ console_route('group_buy_activities.edit', $activity->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $activity->id }}"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('GroupBuy::common.no_data') }}</td></tr>
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
        if (!confirm(@json(__('GroupBuy::common.confirm_delete')))) return;
        fetch('{{ console_route('group_buy_activities.index') }}/' + this.dataset.id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
