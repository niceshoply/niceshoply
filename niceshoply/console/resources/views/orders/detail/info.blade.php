{{-- Order Basic Information --}}
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/order.order_info') }}</h5>
  </div>
  <div class="card-body">
    <div class="order-info-grid row g-2">
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/order.number') }}:</div>
        <p class="mb-0">{{ $order->number }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/order.customer_name') }}:</div>
        <p class="mb-0">{{ $order->customer_name ?? ($order->customer->name ?? '') }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/order.created_at') }}:</div>
        <p class="mb-0">{{ $order->created_at }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/common.status') }}:</div>
        <div>
          <span class="badge bg-{{ $order->status_color }}">{{ $order->status_format }}</span>
        </div>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/order.total') }}:</div>
        <p class="mb-0">{{ $order->total_format }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/order.billing_method_name') }}:</div>
        <p class="mb-0">{{ $order->billing_method_name }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/order.shipping_method_name') }}:</div>
        <p class="mb-0">{{ $order->shipping_method_name }}</p>
      </div>
      <div class="col-lg-3 col-md-4 d-flex">
        <div class="fw-bold me-2">{{ __('console/order.email') }}:</div>
        <p class="mb-0">{{ $order->email }}</p>
      </div>
      @hookinsert('console.orders.detail.order_info.bottom')
    </div>
  </div>
</div>

@hookinsert('console.orders.detail.order_info.after')
