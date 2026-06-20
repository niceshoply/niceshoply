@extends('console::layouts.app')

@section('title', __('GiftCard::common.cards_of').' - '.$batch->name)

@section('content')
  <div class="mb-3">
    <a href="{{ console_route('gift_card.index') }}" class="btn btn-light">{{ __('GiftCard::common.back') }}</a>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('GiftCard::common.code') }}</th>
            <th>{{ __('GiftCard::common.pin') }}</th>
            <th>{{ __('GiftCard::common.balance') }}</th>
            <th>{{ __('GiftCard::common.status') }}</th>
            <th>{{ __('GiftCard::common.customer_id') }}</th>
            <th>{{ __('GiftCard::common.redeemed_at') }}</th>
            <th class="text-end">{{ __('GiftCard::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @foreach($cards as $card)
            <tr>
              <td>{{ $card->id }}</td>
              <td><code>{{ $card->code }}</code></td>
              <td><code>{{ $card->pin }}</code></td>
              <td>{{ currency_format($card->balance) }}</td>
              <td><span class="badge bg-secondary">{{ __('GiftCard::common.st_'.$card->status) }}</span></td>
              <td>{{ $card->customer_id ?: '-' }}</td>
              <td>{{ optional($card->redeemed_at)->format('Y-m-d H:i') }}</td>
              <td class="text-end">
                @if($card->status !== 'used')
                  <button class="btn btn-sm btn-outline-warning btn-toggle" data-id="{{ $card->id }}">
                    {{ $card->status === 'disabled' ? __('GiftCard::common.enable') : __('GiftCard::common.disable') }}
                  </button>
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $cards->links() }}</div>
    </div>
  </div>

  <script>
    document.querySelectorAll('.btn-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        fetch('{{ console_route('gift_card.index') }}/cards/' + this.dataset.id + '/toggle', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
