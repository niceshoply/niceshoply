@php($formHtml = $form_html ?? '')
@php($error = $error ?? '')

<div class="union-pay card w-max-560 m-auto">
  <div class="card-body text-center">
    <div class="fs-5 mb-3">{{ __('UnionPay::common.redirecting') }}</div>

    @if($error)
      <div class="alert alert-danger">{{ $error }}</div>
    @else
      <div class="text-muted small mb-2">{{ __('UnionPay::common.amount') }}：{{ currency_format($order->total) }}</div>
      <div class="text-muted small mb-3">{{ __('UnionPay::common.order_no') }}：{{ $order->number }}</div>
      <div class="spinner-border text-primary" role="status"></div>
      {{-- 银联网关自动提交表单 --}}
      {!! $formHtml !!}
    @endif
  </div>
</div>
