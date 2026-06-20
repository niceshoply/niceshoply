@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/warehouse.stock_movements'))

@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <form class="row g-3 mb-3" action="{{ console_route('warehouse_stock_movements.index') }}" method="GET">
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
        <select name="type" class="form-select">
          <option value="">{{ __('console/warehouse.all_types') }}</option>
          @foreach($types as $type)
          <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary">{{ __('console/common.search') }}</button>
      </div>
    </form>

    @if ($movements->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/warehouse.warehouse') }}</td>
            <td>{{ __('console/warehouse.sku_code') }}</td>
            <td>{{ __('console/warehouse.quantity') }}</td>
            <td>{{ __('console/warehouse.type') }}</td>
            <td>{{ __('console/warehouse.reference') }}</td>
            <td>{{ __('console/warehouse.note') }}</td>
            <td>{{ __('console/common.created_at') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($movements as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->warehouse->name ?? '' }}</td>
            <td>{{ $item->sku_code }}</td>
            <td>
              <span class="{{ $item->quantity > 0 ? 'text-success' : 'text-danger' }}">
                {{ $item->quantity > 0 ? '+' : '' }}{{ $item->quantity }}
              </span>
            </td>
            <td><span class="badge bg-secondary">{{ $item->type }}</span></td>
            <td>{{ $item->reference_type ? $item->reference_type . '#' . $item->reference_id : '-' }}</td>
            <td>{{ $item->note }}</td>
            <td>{{ $item->created_at }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $movements->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
