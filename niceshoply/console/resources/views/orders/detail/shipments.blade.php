{{-- Shipping Information --}}
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ __('console/order.shipping_information') }}</h5>
    <button class="btn btn-sm btn-primary mt-2" id="addRow">{{ __('console/order.add') }}</button>
  </div>
  <div class="card-body">
    <table class="table table-response align-middle table-bordered" id="logisticsTable">
      <thead>
        <tr>
          <td>ID</td>
          <th>{{ __('console/order.express_company') }}</th>
          <th>{{ __('console/order.express_number') }}</th>
          <th>{{ __('console/order.create_time') }}</th>
          <th>{{ __('console/order.operation') }}</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($order->shipments as $shipment)
          <tr>
            <td data-title="id">{{ $shipment->id }}</td>
            <td data-title="express_company">{{ $shipment->express_company }}</td>
            <td data-title="express_number">{{ $shipment->express_number }}</td>
            <td data-title="created_at">{{ $shipment->created_at }}</td>
            <td>
              <button class="btn btn-sm btn-primary deleteRow"
                onclick="deleteShipment('{{ $shipment->id }}')">{{ __('console/order.delete') }}</button>
              <button class="btn btn-sm btn-primary viewRow"
                onclick="viewShipmentDetails('{{ $shipment->id }}')">{{ __('console/order.view') }}</button>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- Shipment Info Modal --}}
<div class="modal fade" id="newShipmentModal" tabindex="-1" aria-labelledby="newShipmentModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newShipmentModalLabel">{{ __('console/order.shipment_information') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table">
          <tbody>
            <tr>
              <th class="col-3">{{ __('console/order.time') }}</th>
              <th class="col-9">{{ __('console/order.logistics_information') }}</th>
            </tr>
          </tbody>
          <tbody>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary"
          data-bs-dismiss="modal">{{ __('console/order.confirm') }}</button>
      </div>
    </div>
  </div>
</div>

{{-- Edit Shipment Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-bs-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">{{ __('console/order.edit_logistics_information') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label for="logisticsCompany" class="form-label">{{ __('console/order.express_company') }}</label>
            <select class="form-control" id="logisticsCompany">
              @foreach (is_array(system_setting('logistics', [])) ? system_setting('logistics', []) : [] as $expressCompany)
                <option value="{{ $expressCompany['code'] }}">{{ $expressCompany['company'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="trackingNumber" class="form-label">{{ __('console/order.express_number') }}</label>
            <input type="text" class="form-control" id="trackingNumber">
          </div>
          <p class="text-muted small mb-0">{{ __('console/order.ship_hint') }}</p>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary"
          data-bs-dismiss="modal">{{ __('console/order.close') }}</button>
        <button type="button" class="btn btn-primary"
          onclick="submitEdit()">{{ __('console/order.save_changes') }}</button>
      </div>
    </div>
  </div>
</div>

@hookinsert('console.orders.detail.shipping.after')
