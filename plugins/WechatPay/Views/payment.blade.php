<div class="card w-max-700 m-auto">
  <div class="card-body text-center">
    @if(!empty($h5_url))
      <p class="text-muted">{{ __('WechatPay::common.redirecting') }}</p>
      <script>window.location.replace(@json($h5_url));</script>
    @else
      <h5 class="mb-3">{{ __('WechatPay::common.title') }}</h5>
      <p class="text-muted">{{ __('WechatPay::common.h5_tip') }}</p>
      <p class="small text-secondary">{{ __('WechatPay::common.order_number') }}: {{ $order->number ?? '' }}</p>
      <p class="small">{{ __('WechatPay::common.amount') }}: {{ currency_format($order->total ?? 0, $order->currency_code ?? '') }}</p>
      <hr>
      <p class="text-muted small">{{ __('WechatPay::common.configure_hint') }}</p>
    @endif
  </div>
</div>
