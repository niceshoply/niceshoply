{{-- Product Information --}}
<div class="card mb-4 h-100">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/order_return.product_info') }}</h5>
  </div>
  <div class="card-body">
    <div class="d-flex">
      @if ($order_return->product)
        <div class="wh-80 overflow-hidden border border-1 rounded me-3 flex-shrink-0">
          <img src="{{ $order_return->product->image_url }}" alt="{{ $order_return->product_name }}" class="img-fluid">
        </div>
      @endif
      <div>
        <div class="mb-1">
          @if ($order_return->product)
            <a href="{{ console_route('products.edit', $order_return->product_id) }}" target="_blank" class="text-decoration-none fw-medium">
              {{ $order_return->product_name }}
            </a>
          @else
            <span class="fw-medium">{{ $order_return->product_name }}</span>
          @endif
        </div>
        <div class="text-muted small mb-1">{{ __('console/order_return.product_sku') }}: {{ $order_return->product_sku ?: '-' }}</div>
        <div class="text-muted small">{{ __('front/return.quantity') }}: {{ $order_return->quantity }}</div>
      </div>
    </div>
    @hookinsert('console.order_returns.detail.product.bottom', $order_return)
  </div>
</div>
