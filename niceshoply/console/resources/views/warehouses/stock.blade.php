@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.warehouse_stocks'))

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <form class="row g-3 mb-3" action="{{ console_route('warehouse_stocks.index') }}" method="GET">
      <div class="col-auto">
        <select name="warehouse_id" class="form-select">
          <option value="">{{ __('console/warehouse.all_warehouses') }}</option>
          @foreach($warehouses as $wh)
          <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <input type="text" name="sku_code" class="form-control" placeholder="{{ __('console/warehouse.sku_code') }}" value="{{ request('sku_code') }}">
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary">{{ __('console/common.search') }}</button>
      </div>
      <div class="col-auto ms-auto d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" id="btn-export-stock">
          <i class="bi bi-file-earmark-excel"></i> {{ __('console/warehouse.export_stock') }}
        </button>
        <button type="button" class="btn btn-outline-secondary" id="btn-import-stock">
          <i class="bi bi-upload"></i> {{ __('console/warehouse.import_stock') }}
        </button>
        <button type="button" class="btn btn-primary" id="btn-add-stock">{{ __('console/warehouse.add_stock') }}</button>
      </div>
    </form>

    @if ($stocks->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td><input type="checkbox" id="check-all" class="form-check-input"></td>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/warehouse.warehouse') }}</td>
            <td>{{ __('console/warehouse.sku_code') }}</td>
            <td>{{ __('console/warehouse.quantity') }}</td>
            <td>{{ __('console/warehouse.reserved') }}</td>
            <td>{{ __('console/warehouse.available') }}</td>
            <td>{{ __('console/warehouse.low_threshold') }}</td>
            <td>{{ __('console/common.updated_at') }}</td>
            <td>{{ __('console/common.action') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($stocks as $item)
          <tr>
            <td><input type="checkbox" class="form-check-input row-check" value="{{ $item->id }}"></td>
            <td>{{ $item->id }}</td>
            <td>{{ $item->warehouse->name ?? '' }}</td>
            <td>{{ $item->sku_code }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->reserved_quantity }}</td>
            <td>{{ $item->available_quantity }}</td>
            <td>{{ $item->low_stock_threshold }}</td>
            <td>{{ $item->updated_at }}</td>
            <td>
              <button type="button" class="btn btn-sm btn-outline-primary btn-adjust"
                data-warehouse-id="{{ $item->warehouse_id }}"
                data-warehouse-name="{{ $item->warehouse->name ?? '' }}"
                data-sku-code="{{ $item->sku_code }}"
                data-quantity="{{ $item->quantity }}">
                {{ __('console/warehouse.adjust_stock') }}
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary btn-history"
                data-warehouse-id="{{ $item->warehouse_id }}"
                data-warehouse-name="{{ $item->warehouse->name ?? '' }}"
                data-sku-code="{{ $item->sku_code }}">
                {{ __('console/warehouse.stock_movements') }}
              </button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $stocks->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>

<form id="export-form" action="{{ console_route('warehouse_stocks.export') }}" method="POST" style="display:none;">
  @csrf
  <input type="hidden" name="ids" id="export-ids">
  @foreach(request()->query() as $key => $val)
  <input type="hidden" name="{{ $key }}" value="{{ $val }}">
  @endforeach
</form>

<div class="modal fade" id="importStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ console_route('warehouse_stocks.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">{{ __('console/warehouse.import_stock') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">{{ __('console/warehouse.import_file') }}</label>
            <input type="file" class="form-control" name="file" accept=".xlsx,.csv,.xls" required>
            <div class="form-text text-muted">{{ __('console/warehouse.import_file_hint') }}</div>
          </div>
          <div>
            <a href="{{ console_route('warehouse_stocks.template') }}" class="text-decoration-none">
              <i class="bi bi-download"></i> {{ __('console/warehouse.download_template') }}
            </a>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('console/common.close') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('console/common.submit') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="addStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ console_route('warehouse_stocks.adjust') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">{{ __('console/warehouse.add_stock') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold" for="add-warehouse-id">{{ __('console/warehouse.warehouse') }}</label>
            <select class="form-select" name="warehouse_id" id="add-warehouse-id" required>
              <option value="">{{ __('console/common.please_choose') }}</option>
              @foreach($warehouses as $wh)
              <option value="{{ $wh->id }}">{{ $wh->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold" for="add-sku-code">{{ __('console/warehouse.sku_code') }}</label>
            <input type="text" class="form-control" name="sku_code" id="add-sku-code" required placeholder="{{ __('console/warehouse.sku_code') }}">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold" for="add-quantity">{{ __('console/warehouse.quantity') }}</label>
            <input type="number" class="form-control" name="quantity" id="add-quantity" required min="1" placeholder="{{ __('console/warehouse.quantity') }}">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold" for="add-note">{{ __('console/warehouse.note') }}</label>
            <input type="text" class="form-control" name="note" id="add-note" placeholder="{{ __('console/warehouse.note') }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('console/common.close') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('console/common.submit') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ console_route('warehouse_stocks.adjust') }}" method="POST">
        @csrf
        <input type="hidden" name="warehouse_id" id="adjust-warehouse-id">
        <input type="hidden" name="sku_code" id="adjust-sku-code">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('console/warehouse.adjust_stock') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">{{ __('console/warehouse.warehouse') }}</label>
            <div id="adjust-warehouse-name" class="form-control-plaintext"></div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">{{ __('console/warehouse.sku_code') }}</label>
            <div id="adjust-sku-display" class="form-control-plaintext"></div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">{{ __('console/warehouse.quantity') }}</label>
            <div id="adjust-current-qty" class="form-control-plaintext"></div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold" for="adjust-quantity">{{ __('console/warehouse.adjust_quantity') }}</label>
            <input type="number" class="form-control" id="adjust-quantity" name="quantity" required placeholder="{{ __('console/warehouse.adjust_quantity') }}">
            <div class="form-text text-muted">{{ __('console/warehouse.adjust_hint') }}</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold" for="adjust-note">{{ __('console/warehouse.note') }}</label>
            <input type="text" class="form-control" id="adjust-note" name="note" placeholder="{{ __('console/warehouse.note') }}">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('console/common.close') }}</button>
          <button type="submit" class="btn btn-primary">{{ __('console/common.submit') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('console/warehouse.stock_movements') }} - <span id="history-title"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="history-loading" class="text-center py-3" style="display:none;">
          <div class="spinner-border spinner-border-sm"></div>
        </div>
        <table class="table table-sm align-middle" id="history-table" style="display:none;">
          <thead>
            <tr>
              <td>{{ __('console/common.id') }}</td>
              <td>{{ __('console/warehouse.quantity') }}</td>
              <td>{{ __('console/warehouse.type') }}</td>
              <td>{{ __('console/warehouse.note') }}</td>
              <td>{{ __('console/common.created_at') }}</td>
            </tr>
          </thead>
          <tbody id="history-tbody"></tbody>
        </table>
        <div id="history-empty" class="text-center text-muted py-3" style="display:none;">{{ __('console/common.no_data') }}</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('console/common.close') }}</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('footer')
<script>
  var importModal = new bootstrap.Modal(document.getElementById('importStockModal'));
  var addModal = new bootstrap.Modal(document.getElementById('addStockModal'));
  var adjustModal = new bootstrap.Modal(document.getElementById('adjustStockModal'));
  var historyModal = new bootstrap.Modal(document.getElementById('historyModal'));

  $('#btn-import-stock').on('click', function() {
    importModal.show();
  });

  // Select all / deselect all
  $('#check-all').on('change', function() {
    $('.row-check').prop('checked', this.checked);
  });
  $(document).on('change', '.row-check', function() {
    var total = $('.row-check').length;
    var checked = $('.row-check:checked').length;
    $('#check-all').prop('checked', total === checked);
    $('#check-all').prop('indeterminate', checked > 0 && checked < total);
  });

  // Export: selected IDs or all current page
  $('#btn-export-stock').on('click', function() {
    var ids = [];
    $('.row-check:checked').each(function() {
      ids.push($(this).val());
    });
    $('#export-ids').val(ids.join(','));
    $('#export-form').submit();
  });

  $('#btn-add-stock').on('click', function() {
    $('#add-warehouse-id').val('');
    $('#add-sku-code').val('');
    $('#add-quantity').val('');
    $('#add-note').val('');
    addModal.show();
  });

  $(document).on('click', '.btn-adjust', function() {
    var btn = $(this);
    $('#adjust-warehouse-id').val(btn.data('warehouse-id'));
    $('#adjust-sku-code').val(btn.data('sku-code'));
    $('#adjust-warehouse-name').text(btn.data('warehouse-name'));
    $('#adjust-sku-display').text(btn.data('sku-code'));
    $('#adjust-current-qty').text(btn.data('quantity'));
    $('#adjust-quantity').val('');
    $('#adjust-note').val('');
    adjustModal.show();
  });

  $(document).on('click', '.btn-history', function() {
    var btn = $(this);
    var warehouseId = btn.data('warehouse-id');
    var skuCode = btn.data('sku-code');
    var warehouseName = btn.data('warehouse-name');
    $('#history-title').text(warehouseName + ' / ' + skuCode);
    $('#history-loading').show();
    $('#history-table').hide();
    $('#history-empty').hide();
    $('#history-tbody').empty();
    historyModal.show();

    $.get('{{ console_route('warehouse_stocks.recent_movements') }}', {warehouse_id: warehouseId, sku_code: skuCode}, function(data) {
      $('#history-loading').hide();
      if (data.length === 0) {
        $('#history-empty').show();
        return;
      }
      $.each(data, function(i, item) {
        var qtyClass = item.quantity > 0 ? 'text-success' : 'text-danger';
        var qtyText = (item.quantity > 0 ? '+' : '') + item.quantity;
        $('#history-tbody').append(
          '<tr><td>' + item.id + '</td>' +
          '<td><span class="' + qtyClass + '">' + qtyText + '</span></td>' +
          '<td><span class="badge bg-secondary">' + item.type + '</span></td>' +
          '<td>' + (item.note || '-') + '</td>' +
          '<td>' + item.created_at + '</td></tr>'
        );
      });
      $('#history-table').show();
    });
  });
</script>
@endpush
