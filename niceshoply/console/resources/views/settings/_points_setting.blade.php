{{-- 积分规则设置 --}}
<div class="tab-pane fade" id="tab-setting-points">
  <x-common-form-switch-radio title="{{ __('console/point.enabled') }}"
    name="points_enabled"
    :value="old('points_enabled', system_setting('points_enabled', false))" />

  <x-common-form-input title="{{ __('console/point.earn_rate') }}" name="points_earn_rate"
    :value="old('points_earn_rate', system_setting('points_earn_rate', 1))"
    description="{{ __('console/point.earn_rate_hint') }}" />

  <x-common-form-input title="{{ __('console/point.redeem_rate') }}" name="points_redeem_rate"
    :value="old('points_redeem_rate', system_setting('points_redeem_rate', 100))"
    description="{{ __('console/point.redeem_rate_hint') }}" />

  <x-common-form-input title="{{ __('console/point.max_redeem_percent') }}" name="points_max_redeem_percent"
    :value="old('points_max_redeem_percent', system_setting('points_max_redeem_percent', 50))"
    description="{{ __('console/point.max_redeem_percent_hint') }}" />

  <x-common-form-input title="{{ __('console/point.expire_days') }}" name="points_expire_days"
    :value="old('points_expire_days', system_setting('points_expire_days', 0))"
    description="{{ __('console/point.expire_days_hint') }}" />
</div>
