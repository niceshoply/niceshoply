<div class="card w-max-700 m-auto">
  <div class="card-body text-center">
    <h5 class="mb-3">{{ __('Alipay::common.title') }}</h5>
    <p class="text-muted">{{ __('Alipay::common.h5_tip') }}</p>
    <p class="small text-secondary">{{ __('Alipay::common.order_number') }}: {{ $order->number ?? '' }}</p>
    <p class="small">{{ __('Alipay::common.amount') }}: {{ currency_format($order->total ?? 0, $order->currency_code ?? '') }}</p>
    @if(!empty($order?->number))
      <a class="btn btn-primary mt-3" href="{{ front_route('alipay_wap', ['number' => $order->number]) }}">
        {{ __('Alipay::common.open_wap') }}
      </a>
    @endif
    <hr>
    <p class="text-muted small">{{ __('Alipay::common.configure_hint') }}</p>
  </div>
</div>
