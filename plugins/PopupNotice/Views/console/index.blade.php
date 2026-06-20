@extends('console::layouts.app')

@section('title', __('PopupNotice::common.menu'))

@section('content')
  <div class="mb-3 text-end">
    <a href="{{ console_route('popup_notice.create') }}" class="btn btn-primary">{{ __('PopupNotice::common.create') }}</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('PopupNotice::common.title') }}</th>
            <th>{{ __('PopupNotice::common.type') }}</th>
            <th>{{ __('PopupNotice::common.scope') }}</th>
            <th>{{ __('PopupNotice::common.start_at') }}</th>
            <th>{{ __('PopupNotice::common.end_at') }}</th>
            <th>{{ __('PopupNotice::common.is_active') }}</th>
            <th class="text-end">{{ __('PopupNotice::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($notices as $n)
            <tr>
              <td>{{ $n->id }}</td>
              <td>{{ $n->title }}</td>
              <td>{{ __('PopupNotice::common.type_'.$n->type) }}</td>
              <td>{{ __('PopupNotice::common.scope_'.$n->scope) }}</td>
              <td>{{ optional($n->start_at)->format('Y-m-d H:i') }}</td>
              <td>{{ optional($n->end_at)->format('Y-m-d H:i') }}</td>
              <td>
                @if($n->is_active)<span class="badge bg-success">{{ __('PopupNotice::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('PopupNotice::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <a href="{{ console_route('popup_notice.edit', $n->id) }}" class="btn btn-sm btn-outline-primary">{{ __('PopupNotice::common.edit') }}</a>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $n->id }}">{{ __('PopupNotice::common.delete') }}</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">{{ __('PopupNotice::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $notices->links() }}</div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm('?')) return;
        const form = new FormData();
        form.append('_method', 'DELETE');
        fetch('{{ console_route('popup_notice.index') }}/' + this.dataset.id, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
