{{-- Inline copy button. Requires: $value --}}
@if (!empty(trim((string) $value)))
  <button type="button" class="btn btn-link btn-sm p-0 ms-1 align-baseline address-copy"
    data-copy="{{ $value }}" title="{{ __('console/order.copy') }}">
    <i class="bi bi-clipboard"></i>
  </button>
@endif
