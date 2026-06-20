{{-- Customer Information --}}
<div class="card mb-4 h-100">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/order_return.customer_info') }}</h5>
  </div>
  <div class="card-body">
    @if ($order_return->customer)
      <div class="d-flex mb-2">
        <div class="fw-bold me-2">{{ __('console/order_return.name') }}:</div>
        <a href="{{ console_route('customers.edit', $order_return->customer_id) }}" target="_blank" class="text-decoration-none">
          {{ $order_return->customer->name }}
        </a>
      </div>
      <div class="d-flex mb-2">
        <div class="fw-bold me-2">{{ __('console/order_return.email') }}:</div>
        <p class="mb-0">{{ $order_return->customer->email }}</p>
      </div>
      <div class="d-flex mb-0">
        <div class="fw-bold me-2">{{ __('console/order_return.telephone') }}:</div>
        <p class="mb-0">{{ $order_return->customer->telephone ?: '-' }}</p>
      </div>
    @else
      <p class="text-muted mb-0">-</p>
    @endif
    @hookinsert('console.order_returns.detail.customer.bottom', $order_return)
  </div>
</div>
