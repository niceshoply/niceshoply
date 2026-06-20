{{-- 合规与风控设置 --}}
<div class="tab-pane fade" id="tab-setting-compliance">
  <h6 class="mb-3">{{ __('console/setting.cookie_banner') }}</h6>
  <x-common-form-switch-radio title="{{ __('console/setting.cookie_banner_enabled') }}"
    name="cookie_banner_enabled"
    :value="old('cookie_banner_enabled', system_setting('cookie_banner_enabled', true))" />

  <hr>
  <h6 class="mb-3">{{ __('console/setting.order_risk') }}</h6>
  <x-common-form-input title="{{ __('console/setting.risk_order_max_amount') }}" name="risk_order_max_amount"
    :value="old('risk_order_max_amount', system_setting('risk_order_max_amount', 10000))" />
  <x-common-form-input title="{{ __('console/setting.risk_high_score_threshold') }}" name="risk_high_score_threshold"
    :value="old('risk_high_score_threshold', system_setting('risk_high_score_threshold', 50))" />
  <x-common-form-input title="{{ __('console/setting.risk_order_frequency_limit') }}" name="risk_order_frequency_limit"
    :value="old('risk_order_frequency_limit', system_setting('risk_order_frequency_limit', 5))" />
  <x-common-form-input title="{{ __('console/setting.risk_order_frequency_hours') }}" name="risk_order_frequency_hours"
    :value="old('risk_order_frequency_hours', system_setting('risk_order_frequency_hours', 1))" />

  <hr>
  <h6 class="mb-3">{{ __('console/setting.order_rate_limit') }}</h6>
  <x-common-form-switch-radio title="{{ __('console/setting.order_rate_limit_enabled') }}"
    name="order_rate_limit_enabled"
    :value="old('order_rate_limit_enabled', system_setting('order_rate_limit_enabled', true))" />
  <x-common-form-input title="{{ __('console/setting.order_rate_limit_customer') }}" name="order_rate_limit_customer"
    :value="old('order_rate_limit_customer', system_setting('order_rate_limit_customer', 10))" />
  <x-common-form-input title="{{ __('console/setting.order_rate_limit_ip') }}" name="order_rate_limit_ip"
    :value="old('order_rate_limit_ip', system_setting('order_rate_limit_ip', 20))" />
  <x-common-form-input title="{{ __('console/setting.order_rate_limit_window_minutes') }}" name="order_rate_limit_window_minutes"
    :value="old('order_rate_limit_window_minutes', system_setting('order_rate_limit_window_minutes', 60))" />

  <hr>
  <x-common-form-switch-radio title="{{ __('console/setting.login_anomaly_alert_enabled') }}"
    name="login_anomaly_alert_enabled"
    :value="old('login_anomaly_alert_enabled', system_setting('login_anomaly_alert_enabled', true))" />
</div>
