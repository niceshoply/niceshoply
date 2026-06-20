@extends('console::layouts.app')

@section('title', __('Membership::common.menu_title'))

@section('page-title-right')
  <a href="{{ console_route('membership_levels.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> {{ __('Membership::common.create') }}
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
            <th>{{ __('Membership::common.name') }}</th>
            <th>{{ __('Membership::common.min_spent') }}</th>
            <th>{{ __('Membership::common.discount_percent') }}</th>
            <th>{{ __('Membership::common.sort') }}</th>
            <th>{{ __('Membership::common.active') }}</th>
            <th class="text-end">{{ __('Membership::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($levels as $level)
            <tr>
              <td>{{ $level->id }}</td>
              <td>{{ $level->name }}</td>
              <td>{{ currency_format($level->min_spent) }}</td>
              <td>{{ $level->discount_percent }}%</td>
              <td>{{ $level->sort }}</td>
              <td>
                @if($level->active)<span class="badge bg-success">{{ __('Membership::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('Membership::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <a href="{{ console_route('membership_levels.edit', $level->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $level->id }}"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('Membership::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $levels->links() }}
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm(@json(__('Membership::common.confirm_delete')))) return;
        fetch('{{ console_route('membership_levels.index') }}/' + this.dataset.id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
