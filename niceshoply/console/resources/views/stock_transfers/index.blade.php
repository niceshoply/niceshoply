@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.stock_transfers'))
@section('page-title-right')
<a href="{{ console_route('stock_transfers.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('stock_transfers.index')" />

    @if ($transfers->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/warehouse.transfer_number') }}</td>
            <td>{{ __('console/warehouse.from_warehouse') }}</td>
            <td>{{ __('console/warehouse.to_warehouse') }}</td>
            <td>{{ __('console/warehouse.status') }}</td>
            <td>{{ __('console/common.created_at') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($transfers as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->number }}</td>
            <td>{{ $item->fromWarehouse->name ?? '' }}</td>
            <td>{{ $item->toWarehouse->name ?? '' }}</td>
            <td><span class="badge bg-{{ $item->status == 'completed' ? 'success' : ($item->status == 'cancelled' ? 'danger' : 'warning') }}">{{ $item->status }}</span></td>
            <td>{{ $item->created_at }}</td>
            <td>
              <a href="{{ console_route('stock_transfers.show', [$item->id]) }}">
                <el-button size="small" plain type="primary">{{ __('console/common.view') }}</el-button>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $transfers->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
