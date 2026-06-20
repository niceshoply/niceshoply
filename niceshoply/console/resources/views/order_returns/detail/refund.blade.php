{{-- Refund Records --}}
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">{{ __('console/order_return.refund_info') }}</h5>
    <div class="d-flex align-items-center">
      @if ($order_return->refund_total > 0)
        <span class="fw-bold text-danger me-3">{{ __('console/order_return.refund_total') }}: {{ currency_format($order_return->refund_total) }}</span>
      @endif
      @if ($order_return->exists)
        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#refundModal">
          <i class="bi bi-cash-coin me-1"></i>{{ __('console/order_return.create_refund') }}
        </button>
      @endif
    </div>
  </div>
  <div class="card-body">
    @if ($order_return->refunds->count())
      <h6 class="text-muted mb-2">{{ __('console/refund.refunds') }}</h6>
      <table class="table align-middle mb-3">
        <thead>
          <tr>
            <th>{{ __('console/refund.number') }}</th>
            <th>{{ __('console/refund.amount') }}</th>
            <th>{{ __('console/refund.method') }}</th>
            <th>{{ __('console/refund.status') }}</th>
            <th>{{ __('console/common.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order_return->refunds as $refundItem)
            <tr>
              <td><code>{{ $refundItem->number }}</code></td>
              <td class="text-danger">{{ currency_format($refundItem->amount, $refundItem->currency_code, $refundItem->currency_value) }}</td>
              <td>{{ __('console/refund.method_'.$refundItem->method) }}</td>
              <td><span class="badge bg-{{ $refundItem->status_color }}">{{ $refundItem->status_format }}</span></td>
              <td>
                <a href="{{ console_route('refunds.show', [$refundItem->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('console/common.detail') }}</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif

    @if ($order_return->payments->count())
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th>{{ __('console/order_return.amount') }}</th>
            <th>{{ __('console/order_return.type') }}</th>
            <th>{{ __('front/return.status') }}</th>
            <th>{{ __('console/order_return.comment') }}</th>
            <th>{{ __('console/order_return.date_time') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($order_return->payments as $payment)
            <tr>
              <td class="text-danger">{{ currency_format($payment->amount) }}</td>
              <td>{{ $payment->type }}</td>
              <td>{{ $payment->status }}</td>
              <td>{{ $payment->comment }}</td>
              <td><span class="text-muted small">{{ $payment->created_at }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @elseif (! $order_return->refunds->count())
      <p class="text-muted mb-0">{{ __('console/order_return.no_refund') }}</p>
    @endif
    @hookinsert('console.order_returns.detail.refund.bottom', $order_return)
  </div>
</div>

{{-- Refund Modal --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('console/order_return.create_refund') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">{{ __('console/order_return.refund_amount') }}</label>
          <input type="number" step="0.01" min="0" id="refundAmount" class="form-control" placeholder="0.00">
        </div>
        <div class="mb-3">
          <label class="form-label">{{ __('console/order_return.refund_type') }}</label>
          <select id="refundType" class="form-select">
            <option value="wallet" {{ $order_return->customer_id ? '' : 'disabled' }}>{{ __('console/order_return.refund_type_wallet') }}</option>
            <option value="original">{{ __('console/order_return.refund_type_original') }}</option>
            <option value="manual">{{ __('console/refund.method_manual') }}</option>
          </select>
          <div class="form-text">{{ __('console/order_return.refund_type_hint') }}</div>
        </div>
        <div class="mb-0">
          <label class="form-label">{{ __('console/order_return.comment') }}</label>
          <textarea id="refundComment" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('console/common.close') }}</button>
        <button type="button" class="btn btn-danger" onclick="submitRefund()">{{ __('console/common.btn_submit') }}</button>
      </div>
    </div>
  </div>
</div>
