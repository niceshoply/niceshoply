@extends('console::layouts.app')

@section('title', __('Coupon::common.menu_title'))

@section('page-title-right')
  <a href="{{ console_route('coupons.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> {{ __('Coupon::common.create') }}
  </a>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <form method="get" class="mb-3 d-flex gap-2" style="max-width:360px">
        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm"
               placeholder="{{ __('Coupon::common.keyword') }}">
        <button class="btn btn-secondary btn-sm">{{ __('Coupon::common.search') }}</button>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Coupon::common.code') }}</th>
            <th>{{ __('Coupon::common.name') }}</th>
            <th>{{ __('Coupon::common.type') }}</th>
            <th>{{ __('Coupon::common.value') }}</th>
            <th>{{ __('Coupon::common.min_amount') }}</th>
            <th>{{ __('Coupon::common.used_count') }}</th>
            <th>{{ __('Coupon::common.active') }}</th>
            <th class="text-end">{{ __('Coupon::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($coupons as $coupon)
            <tr>
              <td>{{ $coupon->id }}</td>
              <td><code>{{ $coupon->code }}</code></td>
              <td>{{ $coupon->name }}</td>
              <td>{{ __('Coupon::common.type_'.$coupon->type) }}</td>
              <td>
                @if($coupon->type === 'percent'){{ $coupon->value }}%
                @elseif($coupon->type === 'free_shipping')—
                @else{{ currency_format($coupon->value) }}@endif
              </td>
              <td>{{ $coupon->min_amount > 0 ? currency_format($coupon->min_amount) : '—' }}</td>
              <td>{{ $coupon->used_count }}{{ $coupon->usage_limit > 0 ? ' / '.$coupon->usage_limit : '' }}</td>
              <td>
                @if($coupon->active)<span class="badge bg-success">{{ __('Coupon::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('Coupon::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <a href="{{ console_route('coupons.edit', $coupon->id) }}" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-pencil"></i>
                </a>
                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $coupon->id }}">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" class="text-center text-muted py-4">{{ __('Coupon::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>

      {{ $coupons->links() }}
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm(@json(__('Coupon::common.confirm_delete')))) return;
        const id = this.dataset.id;
        fetch('{{ console_route('coupons.index') }}/' + id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
