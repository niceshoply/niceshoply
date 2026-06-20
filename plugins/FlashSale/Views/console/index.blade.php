@extends('console::layouts.app')

@section('title', __('FlashSale::common.menu_title'))

@section('page-title-right')
  <a href="{{ console_route('flash_sales.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> {{ __('FlashSale::common.create') }}
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
            <th>{{ __('FlashSale::common.name') }}</th>
            <th>{{ __('FlashSale::common.start_at') }}</th>
            <th>{{ __('FlashSale::common.end_at') }}</th>
            <th>{{ __('FlashSale::common.item_count') }}</th>
            <th>{{ __('FlashSale::common.active') }}</th>
            <th class="text-end">{{ __('FlashSale::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($sales as $sale)
            <tr>
              <td>{{ $sale->id }}</td>
              <td>{{ $sale->name }}</td>
              <td>{{ $sale->start_at?->format('Y-m-d H:i') ?? '-' }}</td>
              <td>{{ $sale->end_at?->format('Y-m-d H:i') ?? '-' }}</td>
              <td>{{ $sale->items_count }}</td>
              <td>
                @if($sale->active)<span class="badge bg-success">{{ __('FlashSale::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('FlashSale::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <a href="{{ console_route('flash_sales.edit', $sale->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $sale->id }}"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('FlashSale::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $sales->links() }}
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm(@json(__('FlashSale::common.confirm_delete')))) return;
        fetch('{{ console_route('flash_sales.index') }}/' + this.dataset.id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
