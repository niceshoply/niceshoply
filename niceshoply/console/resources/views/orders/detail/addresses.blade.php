{{-- Address Information --}}
@php
  $buildFullAddress = function ($parts) {
      return implode("\n", array_filter(array_map('trim', $parts)));
  };

  $shippingRegion = trim(implode(', ', array_filter([
      $order->shipping_city,
      $order->shipping_state,
      $order->shipping_country,
  ])));
  $shippingFull = $buildFullAddress([
      $order->shipping_customer_name,
      $order->shipping_telephone,
      $order->shipping_zipcode,
      trim($order->shipping_address_1 . ' ' . $order->shipping_address_2),
      $shippingRegion,
  ]);

  $billingRegion = trim(implode(', ', array_filter([
      $order->billing_city,
      $order->billing_state,
      $order->billing_country,
  ])));
  $billingFull = $buildFullAddress([
      $order->billing_customer_name,
      $order->billing_telephone,
      $order->billing_zipcode,
      trim($order->billing_address_1 . ' ' . $order->billing_address_2),
      $billingRegion,
  ]);
@endphp

<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/order.address') }}</h5>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-12 col-md-6">
        <div class="address-card">
          <div class="address-card-header mb-3 d-flex justify-content-between align-items-center">
            <h5 class="address-card-title mb-0">{{ __('console/order.shipping_address') }}</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary address-copy"
              data-copy="{{ $shippingFull }}">
              <i class="bi bi-clipboard me-1"></i>{{ __('console/order.copy_full_address') }}
            </button>
          </div>
          <div class="address-card-body">
            <p class="address-line">
              <span><strong>{{ __('common/address.name') }}:</strong> {{ $order->shipping_customer_name }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $order->shipping_customer_name])
            </p>
            <p class="address-line">
              <span><strong>{{ __('common/address.phone') }}:</strong> {{ $order->shipping_telephone }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $order->shipping_telephone])
            </p>
            <p class="address-line">
              <span><strong>{{ __('common/address.zipcode') }}:</strong> {{ $order->shipping_zipcode }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $order->shipping_zipcode])
            </p>
            <p class="address-line">
              <span><strong>{{ __('common/address.address_1') }}:</strong> {{ $order->shipping_address_1 }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $order->shipping_address_1])
            </p>
            @if ($order->shipping_address_2)
              <p class="address-line">
                <span><strong>{{ __('common/address.address_2') }}:</strong> {{ $order->shipping_address_2 }}</span>
                @include('console::orders.detail.partials.copy_btn', ['value' => $order->shipping_address_2])
              </p>
            @endif
            <p class="address-line">
              <span><strong>{{ __('common/address.region') }}:</strong> {{ $shippingRegion }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $shippingRegion])
            </p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="address-card">
          <div class="address-card-header mb-3 d-flex justify-content-between align-items-center">
            <h5 class="address-card-title mb-0">{{ __('console/order.billing_address') }}</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary address-copy"
              data-copy="{{ $billingFull }}">
              <i class="bi bi-clipboard me-1"></i>{{ __('console/order.copy_full_address') }}
            </button>
          </div>
          <div class="address-card-body">
            <p class="address-line">
              <span><strong>{{ __('common/address.name') }}:</strong> {{ $order->billing_customer_name }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $order->billing_customer_name])
            </p>
            <p class="address-line">
              <span><strong>{{ __('common/address.phone') }}:</strong> {{ $order->billing_telephone }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $order->billing_telephone])
            </p>
            <p class="address-line">
              <span><strong>{{ __('common/address.zipcode') }}:</strong> {{ $order->billing_zipcode }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $order->billing_zipcode])
            </p>
            <p class="address-line">
              <span><strong>{{ __('common/address.address_1') }}:</strong> {{ $order->billing_address_1 }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $order->billing_address_1])
            </p>
            @if ($order->billing_address_2)
              <p class="address-line">
                <span><strong>{{ __('common/address.address_2') }}:</strong> {{ $order->billing_address_2 }}</span>
                @include('console::orders.detail.partials.copy_btn', ['value' => $order->billing_address_2])
              </p>
            @endif
            <p class="address-line">
              <span><strong>{{ __('common/address.region') }}:</strong> {{ $billingRegion }}</span>
              @include('console::orders.detail.partials.copy_btn', ['value' => $billingRegion])
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@hookinsert('console.orders.detail.addresses.after')
