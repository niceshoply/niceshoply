@extends('console::layouts.app')
@section('body-class', 'page-refund-detail')
@section('title', __('console/refund.refunds'))

@section('page-title-right')
<a class="btn btn-outline-secondary" href="{{ console_route('refunds.index') }}">
  <i class="bi bi-arrow-left me-1"></i>{{ __('console/common.back') }}
</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ $refund->number }}</h5>
    <span class="badge bg-{{ $refund->status_color }} fs-6 px-3 py-2">{{ $refund->status_format }}</span>
  </div>
  <div class="card-body">
    @if($errors->any())
    <div class="alert alert-danger">
      @foreach($errors->all() as $error)
      <div>{{ $error }}</div>
      @endforeach
    </div>
    @endif

    <div class="row mb-4">
      <div class="col-md-6 mb-3">
        <div class="fw-bold">{{ __('console/refund.order_number') }}</div>
        <div>
          @if($refund->order)
          <a href="{{ console_route('orders.edit', [$refund->order_id]) }}">{{ $refund->order->number }}</a>
          @else
          #{{ $refund->order_id }}
          @endif
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="fw-bold">{{ __('console/refund.customer') }}</div>
        <div>{{ optional($refund->customer)->name ?? '-' }}</div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="fw-bold">{{ __('console/refund.amount') }}</div>
        <div class="text-danger fs-5 fw-bold">{{ currency_format($refund->amount, $refund->currency_code, $refund->currency_value) }}</div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="fw-bold">{{ __('console/refund.method') }}</div>
        <div>{{ __('console/refund.method_'.$refund->method) }}</div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="fw-bold">{{ __('console/refund.gateway') }}</div>
        <div>{{ $refund->gateway ?: '-' }}</div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="fw-bold">{{ __('console/refund.gateway_ref') }}</div>
        <div><code>{{ $refund->gateway_ref ?: '-' }}</code></div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="fw-bold">{{ __('console/refund.reason') }}</div>
        <div>{{ $refund->reason ?: '-' }}</div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="fw-bold">{{ __('console/refund.processed_at') }}</div>
        <div>{{ $refund->processed_at ? $refund->processed_at->format('Y-m-d H:i:s') : '-' }}</div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="fw-bold">{{ __('console/refund.created_at') }}</div>
        <div>{{ $refund->created_at->format('Y-m-d H:i:s') }}</div>
      </div>
    </div>

    @if(in_array($refund->status, ['pending', 'processing'], true))
    <div class="d-flex gap-2 mb-4">
      @if(in_array($refund->status, ['pending', 'processing'], true))
      <el-button type="primary" @click="processRefund">{{ __('console/refund.process') }}</el-button>
      @endif
      @if($refund->status === 'pending')
      <el-button type="danger" plain @click="cancelRefund">{{ __('console/refund.cancel') }}</el-button>
      @endif
    </div>
    @endif

    <h6 class="mb-3">{{ __('console/refund.logs') }}</h6>
    @if($refund->logs->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/refund.from_status') }}</td>
            <td>{{ __('console/refund.to_status') }}</td>
            <td>{{ __('console/refund.comment') }}</td>
            <td>{{ __('console/refund.created_at') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($refund->logs as $log)
          <tr>
            <td>{{ $log->from_status ? trans('common/refund.status_'.$log->from_status) : '-' }}</td>
            <td>{{ trans('common/refund.status_'.$log->to_status) }}</td>
            <td>{{ $log->comment ?: '-' }}</td>
            <td>{{ (string) $log->created_at }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <p class="text-muted">{{ __('console/common.no_data') }}</p>
    @endif
  </div>
</div>
@endsection

@push('footer')
<script>
  const { createApp } = Vue;
  const { ElMessageBox } = ElementPlus;
  const app = createApp({
    methods: {
      processRefund() {
        ElMessageBox.confirm('{{ __('console/refund.process_confirm') }}', '{{ __('console/common.confirm') }}', {
          confirmButtonText: '{{ __('console/common.confirm') }}',
          cancelButtonText: '{{ __('console/common.cancel') }}',
          type: 'warning',
        }).then(() => {
          axios.post('{{ console_route('refunds.process', $refund->id) }}').then((res) => {
            inno.msg(res.message || '{{ __('console/common.updated_success') }}');
            window.location.reload();
          }).catch((err) => {
            const msg = err?.response?.data?.message || '';
            inno.msg(msg || '{{ __('console/common.operation_failed') }}');
          });
        }).catch(() => {});
      },
      cancelRefund() {
        ElMessageBox.confirm('{{ __('console/refund.cancel_confirm') }}', '{{ __('console/common.confirm') }}', {
          confirmButtonText: '{{ __('console/common.confirm') }}',
          cancelButtonText: '{{ __('console/common.cancel') }}',
          type: 'warning',
        }).then(() => {
          axios.post('{{ console_route('refunds.cancel', $refund->id) }}').then((res) => {
            inno.msg(res.message || '{{ __('console/common.updated_success') }}');
            window.location.reload();
          }).catch((err) => {
            const msg = err?.response?.data?.message || '';
            inno.msg(msg || '{{ __('console/common.operation_failed') }}');
          });
        }).catch(() => {});
      },
    },
  });
  app.use(ElementPlus);
  app.mount('#app');
</script>
@endpush
