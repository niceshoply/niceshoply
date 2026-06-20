@extends('console::layouts.app')

@section('title', __('Presale::common.menu'))

@section('content')
  <div class="mb-3 text-end">
    <a href="{{ console_route('presale.create') }}" class="btn btn-primary">{{ __('Presale::common.create') }}</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Presale::common.name') }}</th>
            <th>{{ __('Presale::common.start_at') }}</th>
            <th>{{ __('Presale::common.end_at') }}</th>
            <th>{{ __('Presale::common.ship_date') }}</th>
            <th>{{ __('Presale::common.item_count') }}</th>
            <th>{{ __('Presale::common.active') }}</th>
            <th class="text-end">{{ __('Presale::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($activities as $a)
            <tr>
              <td>{{ $a->id }}</td>
              <td>{{ $a->name }}</td>
              <td>{{ optional($a->start_at)->format('Y-m-d H:i') }}</td>
              <td>{{ optional($a->end_at)->format('Y-m-d H:i') }}</td>
              <td>{{ optional($a->ship_date)->toDateString() }}</td>
              <td>{{ $a->items_count }}</td>
              <td>
                @if($a->active)<span class="badge bg-success">{{ __('Presale::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('Presale::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <a href="{{ console_route('presale.edit', $a->id) }}" class="btn btn-sm btn-outline-primary">{{ __('Presale::common.edit') }}</a>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $a->id }}">{{ __('Presale::common.delete') }}</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">{{ __('Presale::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $activities->links() }}</div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm('?')) return;
        const form = new FormData();
        form.append('_method', 'DELETE');
        fetch('{{ console_route('presale.index') }}/' + this.dataset.id, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
