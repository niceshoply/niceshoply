@extends('console::layouts.app')
@section('title', __('console/menu.order_returns'))

@section('page-title-right')
  <div class="title-right-btns">
    <a class="btn btn-outline-secondary ms-2" href="{{ console_route('order_returns.index') }}">
      <i class="bi bi-arrow-left me-1"></i>{{ __('console/order_return.back_to_list') }}
    </a>
    <div class="status-wrap d-inline" id="status-app">
      @foreach ($next_statuses as $status)
        <button class="btn btn-primary ms-2" @click="edit('{{ $status['status'] }}')">{{ $status['name'] }}</button>
      @endforeach
      @hookinsert('console.order_returns.detail.action.after', $order_return)

      <el-dialog v-model="statusDialog" title="{{ __('console/order_return.change_status') }}" width="500">
        <div class="mb-2">{{ __('console/order_return.next_status') }}</div>
        <div class="mb-3"><span class="badge bg-primary">@{{ statusName }}</span></div>
        <div class="mb-2">{{ __('console/order_return.comment') }}</div>
        <textarea v-model="comment" class="form-control" placeholder="{{ __('console/order_return.comment') }}" rows="3"></textarea>
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
  @include('console::order_returns.detail.info')

  <div class="row">
    <div class="col-lg-6">
      @include('console::order_returns.detail.customer')
    </div>
    <div class="col-lg-6">
      @include('console::order_returns.detail.product')
    </div>
  </div>

  @include('console::order_returns.detail.refund')

  @include('console::order_returns.detail.history')

  @hookinsert('console.order_returns.detail.bottom', $order_return)
@endsection

@push('footer')
  @include('console::order_returns.detail.scripts')
@endpush
