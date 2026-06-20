{{-- Return Basic Information --}}
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ __('console/order_return.return_info') }}</h5>
    <span class="badge bg-{{ $order_return->status_color }}">{{ $order_return->status_format }}</span>
  </div>
  <div class="card-body">
    <div class="order-info-grid row g-2">
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('front/return.number') }}:</div>
        <p class="mb-0">{{ $order_return->number }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/order_return.order_number') }}:</div>
        <p class="mb-0">
          <a href="{{ console_route('orders.show', $order_return->order_id) }}" target="_blank" class="text-decoration-none">{{ $order_return->order_number }}</a>
        </p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('front/return.quantity') }}:</div>
        <p class="mb-0">{{ $order_return->quantity }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('front/return.opened') }}:</div>
        <p class="mb-0">{{ $order_return->opened_format }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/return_reason.return_reason') }}:</div>
        <p class="mb-0">{{ $order_return->reason_name ?: '-' }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('front/return.created_at') }}:</div>
        <p class="mb-0">{{ $order_return->created_at }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/order_return.updated_at') }}:</div>
        <p class="mb-0">{{ $order_return->updated_at }}</p>
      </div>
      @hookinsert('console.order_returns.detail.info.bottom', $order_return)
    </div>

    @if ($order_return->comment)
      <hr/>
      <div class="d-flex">
        <div class="fw-bold me-2">{{ __('console/order_return.return_reason_detail') }}:</div>
        <p class="mb-0 text-muted">{{ $order_return->comment }}</p>
      </div>
    @endif
  </div>
</div>

@hookinsert('console.order_returns.detail.info.after', $order_return)
