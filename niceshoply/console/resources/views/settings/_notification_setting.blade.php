<!-- Notification (外部告警) Settings -->
<div class="tab-pane fade" id="tab-setting-notification">
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('console/setting.notification_settings') }}</h5>
      <p class="text-muted small mb-0">{{ __('console/setting.notification_settings_desc') }}</p>
    </div>
    <div class="card-body">
      <x-common-form-switch-radio title="{{ __('console/setting.notification_enabled') }}" name="notification_enabled"
        value="{{ old('notification_enabled', system_setting('notification_enabled')) }}" />

      <div class="row">
        <div class="col-md-4">
          @php($notificationTypes=[
            ['value' => 'generic',     'label' => __('console/setting.notification_type_generic')],
            ['value' => 'slack',       'label' => 'Slack'],
            ['value' => 'wechat_work', 'label' => __('console/setting.notification_type_wechat_work')],
            ['value' => 'dingtalk',    'label' => __('console/setting.notification_type_dingtalk')],
          ])
          <x-common-form-select title="{{ __('console/setting.notification_webhook_type') }}" name="notification_webhook_type"
            :options="$notificationTypes"
            value="{{ old('notification_webhook_type', system_setting('notification_webhook_type', 'generic')) }}" />
        </div>
        <div class="col-md-8">
          <x-common-form-input title="{{ __('console/setting.notification_webhook_url') }}" name="notification_webhook_url"
            value="{{ old('notification_webhook_url', system_setting('notification_webhook_url')) }}"
            placeholder="https://" />
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('console/setting.notification_events') }}</h5>
      <p class="text-muted small mb-0">{{ __('console/setting.notification_events_desc') }}</p>
    </div>
    <div class="card-body">
      <input type="hidden" name="notification_events[]" value="">
      <div class="row">
        @foreach ([
          'new_order'       => __('console/setting.notification_event_new_order'),
          'order_paid'      => __('console/setting.notification_event_order_paid'),
          'low_stock'       => __('console/setting.notification_event_low_stock'),
          'abandoned_cart'  => __('console/setting.notification_event_abandoned_cart'),
          'high_risk_order' => __('console/setting.notification_event_high_risk_order'),
          'login_anomaly'   => __('console/setting.notification_event_login_anomaly'),
        ] as $code => $label)
          <div class="col-md-4">
            <div class="mb-4">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="notification_events[]" value="{{ $code }}"
                  id="notify_event_{{ $code }}"
                  {{ in_array($code, old('notification_events', system_setting('notification_events', []))) ? 'checked' : '' }}>
                <label class="form-check-label" for="notify_event_{{ $code }}">{{ $label }}</label>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
