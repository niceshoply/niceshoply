@extends('console::layouts.app')

@section('title', __('CartRule::common.menu_title'))

@section('page-title-right')
  <a href="{{ console_route('cart_rules.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> {{ __('CartRule::common.create') }}
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
            <th>{{ __('CartRule::common.name') }}</th>
            <th>{{ __('CartRule::common.min_amount') }}</th>
            <th>{{ __('CartRule::common.discount_type') }}</th>
            <th>{{ __('CartRule::common.discount_value') }}</th>
            <th>{{ __('CartRule::common.active') }}</th>
            <th class="text-end">{{ __('CartRule::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($rules as $rule)
            <tr>
              <td>{{ $rule->id }}</td>
              <td>{{ $rule->name }}</td>
              <td>{{ currency_format($rule->min_amount) }}</td>
              <td>{{ __('CartRule::common.type_'.$rule->discount_type) }}</td>
              <td>{{ $rule->discount_type === 'percent' ? $rule->discount_value.'%' : currency_format($rule->discount_value) }}</td>
              <td>
                @if($rule->active)<span class="badge bg-success">{{ __('CartRule::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('CartRule::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <a href="{{ console_route('cart_rules.edit', $rule->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $rule->id }}"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('CartRule::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $rules->links() }}
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm(@json(__('CartRule::common.confirm_delete')))) return;
        fetch('{{ console_route('cart_rules.index') }}/' + this.dataset.id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
