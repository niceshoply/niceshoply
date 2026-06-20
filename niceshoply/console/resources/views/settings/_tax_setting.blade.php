{{-- 税务与多币种设置 --}}
<div class="tab-pane fade" id="tab-setting-tax">
  <x-common-form-switch-radio title="{{ __('console/setting.tax_base_include_discount') }}"
    name="tax_base_include_discount"
    :value="old('tax_base_include_discount', system_setting('tax_base_include_discount', false))" />

  <x-common-form-select title="{{ __('console/setting.tax_display_mode') }}" name="tax_display_mode"
    :options="[
      ['code' => 'exclusive', 'label' => __('console/setting.tax_display_exclusive')],
      ['code' => 'inclusive', 'label' => __('console/setting.tax_display_inclusive')],
    ]" key="code" label="label"
    value="{{ old('tax_display_mode', system_setting('tax_display_mode', 'exclusive')) }}" />

  <x-common-form-switch-radio title="{{ __('console/setting.currency_auto_update') }}"
    name="currency_auto_update"
    :value="old('currency_auto_update', system_setting('currency_auto_update', true))" />
</div>
