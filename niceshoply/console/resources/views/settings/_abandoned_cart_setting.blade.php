{{-- 弃购挽回设置 --}}
<div class="tab-pane fade" id="tab-setting-abandoned-cart">
  <x-common-form-switch-radio title="{{ __('console/abandoned_cart.enabled') }}"
    name="abandoned_cart_enabled"
    :value="old('abandoned_cart_enabled', system_setting('abandoned_cart_enabled', false))" />

  <x-common-form-input title="{{ __('console/abandoned_cart.idle_hours') }}" name="abandoned_cart_idle_hours"
    :value="old('abandoned_cart_idle_hours', system_setting('abandoned_cart_idle_hours', 24))"
    description="{{ __('console/abandoned_cart.idle_hours_hint') }}" />

  <x-common-form-input title="{{ __('console/abandoned_cart.max_reminders') }}" name="abandoned_cart_max_reminders"
    :value="old('abandoned_cart_max_reminders', system_setting('abandoned_cart_max_reminders', 3))" />

  <x-common-form-input title="{{ __('console/abandoned_cart.reminder_interval_hours') }}" name="abandoned_cart_reminder_interval_hours"
    :value="old('abandoned_cart_reminder_interval_hours', system_setting('abandoned_cart_reminder_interval_hours', 24))" />

  <hr>
  <h6 class="mb-3">{{ __('console/abandoned_cart.coupon_section') }}</h6>

  <x-common-form-switch-radio title="{{ __('console/abandoned_cart.coupon_enabled') }}"
    name="abandoned_cart_coupon_enabled"
    :value="old('abandoned_cart_coupon_enabled', system_setting('abandoned_cart_coupon_enabled', false))" />

  <x-common-form-select title="{{ __('console/abandoned_cart.coupon_type') }}" name="abandoned_cart_coupon_type"
    :options="[
      ['code' => 'percent', 'label' => __('console/coupon.type_percent')],
      ['code' => 'fixed', 'label' => __('console/coupon.type_fixed')],
    ]" key="code" label="label"
    value="{{ old('abandoned_cart_coupon_type', system_setting('abandoned_cart_coupon_type', 'percent')) }}" />

  <x-common-form-input title="{{ __('console/abandoned_cart.coupon_value') }}" name="abandoned_cart_coupon_value"
    :value="old('abandoned_cart_coupon_value', system_setting('abandoned_cart_coupon_value', 10))" />

  <x-common-form-input title="{{ __('console/abandoned_cart.coupon_min_amount') }}" name="abandoned_cart_coupon_min_amount"
    :value="old('abandoned_cart_coupon_min_amount', system_setting('abandoned_cart_coupon_min_amount', 0))" />
</div>
