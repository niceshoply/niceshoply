@php($customer = current_customer())
@php($balance = (float) ($customer->balance ?? 0))
@php($total = (float) $order->total)
@php($enough = $balance >= $total)

<div class="balance-pay card w-max-560 m-auto">
  <div class="card-body">
    <div class="fs-5 mb-3 text-center">{{ __('BalancePay::common.title') }}</div>

    @if(session('error'))
      <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <ul class="list-group mb-3">
      <li class="list-group-item d-flex justify-content-between">
        <span>{{ __('BalancePay::common.order_no') }}</span><span>{{ $order->number }}</span>
      </li>
      <li class="list-group-item d-flex justify-content-between">
        <span>{{ __('BalancePay::common.amount') }}</span><span class="fw-bold">{{ currency_format($total) }}</span>
      </li>
      <li class="list-group-item d-flex justify-content-between">
        <span>{{ __('BalancePay::common.balance') }}</span>
        <span class="{{ $enough ? 'text-success' : 'text-danger' }}">{{ currency_format($balance) }}</span>
      </li>
    </ul>

    @if($enough)
      <form method="POST" action="{{ front_route('balance_pay.confirm') }}">
        @csrf
        <input type="hidden" name="order_number" value="{{ $order->number }}">
        <button type="submit" class="btn btn-success w-100">{{ __('BalancePay::common.confirm_pay') }}</button>
      </form>
    @else
      <div class="alert alert-warning">{{ __('BalancePay::common.insufficient') }}</div>
    @endif
  </div>
</div>
