@extends('console::layouts.app')
@section('body-class', 'page-refund')

@section('title', __('console/refund.refunds'))

@section('page-title-right')
<el-button type="primary" @click="dialogVisible = true"><i class="bi bi-plus-square me-1"></i>
  {{ __('console/refund.create') }}</el-button>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('refunds.index')" />

    @if ($refunds->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/refund.number') }}</td>
            <td>{{ __('console/refund.order_number') }}</td>
            <td>{{ __('console/refund.customer') }}</td>
            <td>{{ __('console/refund.amount') }}</td>
            <td>{{ __('console/refund.method') }}</td>
            <td>{{ __('console/refund.status') }}</td>
            <td>{{ __('console/refund.created_at') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($refunds as $item)
          <tr>
            <td><code>{{ $item->number }}</code></td>
            <td>{{ optional($item->order)->number ?? $item->order_id }}</td>
            <td>{{ optional($item->customer)->name ?? '-' }}</td>
            <td>{{ currency_format($item->amount, $item->currency_code, $item->currency_value) }}</td>
            <td>{{ __('console/refund.method_'.$item->method) }}</td>
            <td><span class="badge bg-{{ $item->status_color }}">{{ $item->status_format }}</span></td>
            <td>{{ (string) $item->created_at }}</td>
            <td>
              <a href="{{ console_route('refunds.show', [$item->id]) }}">
                <el-button size="small" plain type="primary">{{ __('console/common.detail') }}</el-button>
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $refunds->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif

    <el-dialog v-model="dialogVisible" title="{{ __('console/refund.create') }}" width="480">
      <form action="{{ console_route('refunds.store') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label">{{ __('console/refund.order_id') }}</label>
          <input type="number" class="form-control" name="order_id" required />
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('console/refund.amount') }}</label>
          <input type="number" step="0.01" class="form-control" name="amount" required />
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('console/refund.method') }}</label>
          <select class="form-select" name="method">
            <option value="original">{{ __('console/refund.method_original') }}</option>
            <option value="balance">{{ __('console/refund.method_balance') }}</option>
            <option value="manual">{{ __('console/refund.method_manual') }}</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('console/refund.reason') }}</label>
          <input type="text" class="form-control" name="reason" />
        </div>
        <div class="text-end">
          <button type="submit" class="btn btn-primary">{{ __('console/common.submit') }}</button>
        </div>
      </form>
    </el-dialog>
  </div>
</div>
@endsection

@push('footer')
<script>
  const { createApp, ref } = Vue;
  const app = createApp({
    setup() {
      const dialogVisible = ref(false);
      return { dialogVisible };
    }
  });
  app.use(ElementPlus);
  app.mount('#app');
</script>
@endpush
