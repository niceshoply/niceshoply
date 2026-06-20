@extends('console::layouts.app')

@section('title', __('GiftCard::common.menu'))

@section('content')
  <div class="card mb-3">
    <div class="card-body">
      <form id="gen-form" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">{{ __('GiftCard::common.name') }}</label>
          <input name="name" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('GiftCard::common.face_value') }}</label>
          <input name="face_value" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('GiftCard::common.quantity') }}</label>
          <input name="quantity" type="number" class="form-control" value="100" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('GiftCard::common.prefix') }}</label>
          <input name="prefix" class="form-control" placeholder="GC">
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('GiftCard::common.expire_at') }}</label>
          <input name="expire_at" type="date" class="form-control">
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary w-100">{{ __('GiftCard::common.generate') }}</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('GiftCard::common.name') }}</th>
            <th>{{ __('GiftCard::common.face_value') }}</th>
            <th>{{ __('GiftCard::common.created_count') }}</th>
            <th>{{ __('GiftCard::common.expire_at') }}</th>
            <th class="text-end">{{ __('GiftCard::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($batches as $b)
            <tr>
              <td>{{ $b->id }}</td>
              <td>{{ $b->name }}</td>
              <td>{{ currency_format($b->face_value) }}</td>
              <td>{{ $b->cards_count }}</td>
              <td>{{ optional($b->expire_at)->toDateString() }}</td>
              <td class="text-end">
                <a href="{{ console_route('gift_card.cards', $b->id) }}" class="btn btn-sm btn-outline-primary">{{ __('GiftCard::common.view_cards') }}</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('GiftCard::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $batches->links() }}</div>
    </div>
  </div>

  <script>
    document.getElementById('gen-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      fetch('{{ console_route('gift_card.generate') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(function (res) {
        alert(res.message || 'done');
        if (res.success) location.reload();
      });
    });
  </script>
@endsection
