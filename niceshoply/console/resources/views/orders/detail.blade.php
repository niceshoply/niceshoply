@extends('console::layouts.app')
@section('title', __('console/menu.orders'))
@section('page-eyebrow', __('console/menu.orders'))
@section('page-subtitle', __('console/order.detail_subtitle'))

@section('page-title-right')
  <div class="title-right-btns">
    <div class="status-wrap" id="status-app">
      @foreach ($next_statuses as $status)
        <button class="btn btn-primary ms-2" @click="edit('{{ $status['status'] }}')">{{ $status['name'] }}</button>
      @endforeach

      <a class="btn btn-success ms-2" href="{{ console_route('orders.printing', $order) }}"
        target="_blank"><i class="bi bi-printer me-1"></i>{{ console_trans('order.print') }}</a>
      @hookinsert('console.orders.detail.print.after')

      <el-dialog v-model="statusDialog" title="{{ __('console/order.status') }}" width="500">
        <template v-if="needShipment">
          <div class="mb-2">{{ __('console/order.express_company') }}</div>
          <select v-model="expressCode" class="form-control mb-3">
            <option value="">{{ __('console/order.express_company') }}</option>
            <option v-for="company in expressCompanies" :key="company.code" :value="company.code">@{{ company.company }}</option>
          </select>
          <div class="mb-2">{{ __('console/order.express_number') }}</div>
          <input v-model="expressNumber" type="text" class="form-control mb-3"
            placeholder="{{ __('console/order.express_number') }}">
          <p class="text-muted small">{{ __('console/order.ship_hint') }}</p>
        </template>
        <div class="mb-2">{{ __('console/order.comment') }}</div>
        <textarea v-model="comment" class="form-control" placeholder="{{ __('console/order.comment') }}" rows="3"></textarea>
        <template #footer>
          <div class="dialog-footer">
            <el-button @click="statusDialog = false">{{ __('console/common.close') }}</el-button>
            <el-button type="primary" @click="submit">{{ __('console/common.btn_save') }}</el-button>
          </div>
        </template>
      </el-dialog>
    </div>
  </div>
@endsection

@section('content')
  {{-- Order Info --}}
  @include('console::orders.detail.info')

  {{-- Order Items --}}
  @include('console::orders.detail.items')

  {{-- Addresses --}}
  @include('console::orders.detail.addresses')

  {{-- Payments --}}
  @include('console::orders.detail.payments')

  {{-- Shipments --}}
  @include('console::orders.detail.shipments')

  {{-- Comments --}}
  @include('console::orders.detail.comments')

  {{-- History --}}
  @include('console::orders.detail.history')

  {{-- Bundle Modal --}}
  @include('console::orders.bundle.modal')

@endsection

@push('footer')
  @include('console::orders.detail.scripts')
  @include('console::orders.bundle.scripts')
@endpush
