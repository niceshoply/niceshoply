<div class="tab-pane fade" id="tab-setting-warehouse">
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('console/warehouse.warehouse_settings') }}</h5>
    </div>
    <div class="card-body">
      <x-common-form-switch-radio title="{{ __('console/warehouse.warehouse_enabled') }}" name="warehouse_enabled"
        value="{{ old('warehouse_enabled', system_setting('warehouse_enabled')) }}" />

      <x-common-form-select title="{{ __('console/warehouse.allocation_strategy') }}" name="warehouse_allocation_strategy"
        :options="[
          ['value' => 'priority', 'label' => __('console/warehouse.strategy_priority')],
          ['value' => 'nearest', 'label' => __('console/warehouse.strategy_nearest')],
          ['value' => 'stock_first', 'label' => __('console/warehouse.strategy_stock_first')],
          ['value' => 'cost_optimal', 'label' => __('console/warehouse.strategy_cost_optimal')],
        ]"
        value="{{ old('warehouse_allocation_strategy', system_setting('warehouse_allocation_strategy', 'priority')) }}" />

      <x-common-form-switch-radio title="{{ __('console/warehouse.allow_split_shipment') }}" name="warehouse_allow_split_shipment"
        value="{{ old('warehouse_allow_split_shipment', system_setting('warehouse_allow_split_shipment', true)) }}" />
    </div>
  </div>
</div>
