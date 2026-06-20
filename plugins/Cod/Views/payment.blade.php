@php($notice = plugin_setting('Cod', 'notice', ''))

<div class="cod-pay card w-max-560 m-auto">
  <div class="card-body text-center">
    <div class="mb-3"><i class="bi bi-cash-coin" style="font-size:2.5rem;color:#28a745"></i></div>
    <div class="fs-5 mb-2">{{ __('Cod::common.placed_title') }}</div>
    <div class="text-muted mb-3">{{ __('Cod::common.placed_desc') }}</div>

    <ul class="list-group mb-3 text-start">
      <li class="list-group-item d-flex justify-content-between">
        <span>{{ __('Cod::common.order_no') }}</span><span>{{ $order->number }}</span>
      </li>
      <li class="list-group-item d-flex justify-content-between">
        <span>{{ __('Cod::common.amount') }}</span><span class="fw-bold">{{ currency_format($order->total) }}</span>
      </li>
    </ul>

    @if($notice)
      <div class="alert alert-info text-start">{!! nl2br(e($notice)) !!}</div>
    @endif

    <a href="{{ front_route('payment.success') }}?order_number={{ $order->number }}" class="btn btn-success w-100">
      {{ __('Cod::common.view_order') }}
    </a>
  </div>
</div>
