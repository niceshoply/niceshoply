@extends('console::layouts.app')

@section('title', __('Points::common.menu_title'))

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Points::common.customer_id') }}</th>
            <th>{{ __('Points::common.balance') }}</th>
            <th>{{ __('Points::common.total_earned') }}</th>
            <th>{{ __('Points::common.total_spent') }}</th>
            <th class="text-end">{{ __('Points::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($accounts as $account)
            <tr>
              <td>{{ $account->id }}</td>
              <td>{{ $account->customer_id }}</td>
              <td><strong>{{ $account->balance }}</strong></td>
              <td>{{ $account->total_earned }}</td>
              <td>{{ $account->total_spent }}</td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary btn-adjust" data-id="{{ $account->customer_id }}">
                  {{ __('Points::common.adjust') }}
                </button>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('Points::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $accounts->links() }}
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-adjust').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const customerId = this.dataset.id;
        const change = prompt(@json(__('Points::common.adjust')) + ' (+/-)');
        if (change === null || change === '') return;
        const form = new FormData();
        form.set('customer_id', customerId);
        form.set('change', change);
        fetch('{{ console_route('points.adjust') }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
