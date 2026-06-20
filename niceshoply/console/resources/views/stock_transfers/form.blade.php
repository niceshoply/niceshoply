@extends('console::layouts.app')

@section('title', __('console/menu.stock_transfers'))

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ $transfer->id ? __('console/warehouse.transfer_detail') : __('console/warehouse.create_transfer') }}</h5>
  </div>
  <div class="card-body">
    @if(!$transfer->id)
    <form class="needs-validation" novalidate id="app-form" action="{{ console_route('stock_transfers.store') }}" method="POST">
      @csrf
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">{{ __('console/warehouse.from_warehouse') }}</label>
        <div class="col-sm-4">
          <select name="from_warehouse_id" class="form-select" required>
            <option value="">--</option>
            @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">{{ __('console/warehouse.to_warehouse') }}</label>
        <div class="col-sm-4">
          <select name="to_warehouse_id" class="form-select" required>
            <option value="">--</option>
            @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">{{ __('console/warehouse.note') }}</label>
        <div class="col-sm-6">
          <textarea name="note" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">{{ __('console/warehouse.items') }}</label>
        <div class="col-sm-8">
          <div class="mb-2">
            <div class="row g-2">
              <div class="col-5"><input type="text" name="items[0][sku_code]" class="form-control" placeholder="SKU Code" required></div>
              <div class="col-3"><input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="1" required></div>
            </div>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">{{ __('console/common.submit') }}</button>
    </form>
    @else
    <div class="row mb-3">
      <div class="col-sm-6">
        <p><strong>{{ __('console/warehouse.transfer_number') }}:</strong> {{ $transfer->number }}</p>
        <p><strong>{{ __('console/warehouse.from_warehouse') }}:</strong> {{ $transfer->fromWarehouse->name ?? '' }}</p>
        <p><strong>{{ __('console/warehouse.to_warehouse') }}:</strong> {{ $transfer->toWarehouse->name ?? '' }}</p>
        <p><strong>{{ __('console/warehouse.status') }}:</strong> <span class="badge bg-info">{{ $transfer->status }}</span></p>
        <p><strong>{{ __('console/warehouse.note') }}:</strong> {{ $transfer->note }}</p>
      </div>
    </div>
    <h6>{{ __('console/warehouse.items') }}</h6>
    <table class="table">
      <thead><tr><td>SKU</td><td>{{ __('console/warehouse.quantity') }}</td><td>{{ __('console/warehouse.received') }}</td></tr></thead>
      <tbody>
        @foreach($transfer->items as $item)
        <tr><td>{{ $item->sku_code }}</td><td>{{ $item->quantity }}</td><td>{{ $item->received_quantity }}</td></tr>
        @endforeach
      </tbody>
    </table>
    <div class="d-flex gap-2 mt-3">
      @if($transfer->status == 'pending')
      <form action="{{ console_route('stock_transfers.ship', $transfer->id) }}" method="POST">@csrf @method('PUT')
        <button type="submit" class="btn btn-primary">{{ __('console/warehouse.ship') }}</button>
      </form>
      <form action="{{ console_route('stock_transfers.cancel', $transfer->id) }}" method="POST">@csrf @method('PUT')
        <button type="submit" class="btn btn-danger">{{ __('console/common.cancel') }}</button>
      </form>
      @endif
      @if($transfer->status == 'in_transit')
      <form action="{{ console_route('stock_transfers.complete', $transfer->id) }}" method="POST">@csrf @method('PUT')
        <button type="submit" class="btn btn-success">{{ __('console/warehouse.complete') }}</button>
      </form>
      @endif
    </div>
    @endif
  </div>
</div>
@endsection
