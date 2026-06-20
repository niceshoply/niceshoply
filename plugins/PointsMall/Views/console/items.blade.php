@extends('console::layouts.app')

@section('title', __('PointsMall::common.menu_items'))

@section('content')
  <div class="mb-3 text-end">
    <a href="{{ console_route('points_mall.items.create') }}" class="btn btn-primary">{{ __('PointsMall::common.create') }}</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('PointsMall::common.title') }}</th>
            <th>{{ __('PointsMall::common.type') }}</th>
            <th>{{ __('PointsMall::common.points_cost') }}</th>
            <th>{{ __('PointsMall::common.cash_cost') }}</th>
            <th>{{ __('PointsMall::common.stock') }}</th>
            <th>{{ __('PointsMall::common.redeemed_count') }}</th>
            <th>{{ __('PointsMall::common.is_active') }}</th>
            <th class="text-end">{{ __('PointsMall::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($items as $item)
            <tr>
              <td>{{ $item->id }}</td>
              <td>{{ $item->title }}</td>
              <td>{{ __('PointsMall::common.type_'.$item->type) }}</td>
              <td>{{ $item->points_cost }}</td>
              <td>{{ currency_format($item->cash_cost) }}</td>
              <td>{{ $item->stock }}</td>
              <td>{{ $item->redeemed_count }}</td>
              <td>
                @if($item->is_active)<span class="badge bg-success">{{ __('PointsMall::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('PointsMall::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <a href="{{ console_route('points_mall.items.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">{{ __('PointsMall::common.edit') }}</a>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $item->id }}">{{ __('PointsMall::common.delete') }}</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-muted py-4">{{ __('PointsMall::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $items->links() }}</div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm('?')) return;
        const form = new FormData();
        form.append('_method', 'DELETE');
        fetch('{{ console_route('points_mall.items') }}/' + this.dataset.id, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
