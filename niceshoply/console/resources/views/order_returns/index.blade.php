@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.order_returns'))

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      {{-- Status statistics tabs --}}
      <ul class="nav nav-pills order-returns-status-tabs gap-2 mb-3">
        @php
          $statusTabs = collect([['status' => '', 'name' => __('console/order_return.all')]])
              ->merge($all_statuses ?? []);
        @endphp
        @foreach ($statusTabs as $tab)
          @php
            $query = array_merge(request()->except(['page', 'status']), $tab['status'] !== '' ? ['status' => $tab['status']] : []);
            $active = (string) ($current_status ?? '') === (string) $tab['status'];
            $count = $status_counts[$tab['status']] ?? 0;
          @endphp
          <li class="nav-item">
            <a class="nav-link {{ $active ? 'active' : 'bg-light text-dark' }}"
               href="{{ console_route('order_returns.index') }}?{{ http_build_query($query) }}">
              {{ $tab['name'] }}
              <span class="badge rounded-pill {{ $active ? 'bg-white text-primary' : 'bg-secondary' }}">{{ $count }}</span>
            </a>
          </li>
        @endforeach
      </ul>

      <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('order_returns.index')"/>
      @hookinsert('console.order_returns.index.criteria.after')

      {{-- Toolbar: bulk actions + export --}}
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="d-flex flex-wrap align-items-center gap-2">
          <span id="selectedCount" class="text-muted small">{{ __('console/order_return.selected_count', ['count' => 0]) }}</span>
          <select id="bulkStatus" class="form-select form-select-sm w-auto">
            <option value="">{{ __('console/order_return.bulk_change_status') }}</option>
            @foreach (($all_statuses ?? []) as $st)
              <option value="{{ $st['status'] }}">{{ $st['name'] }}</option>
            @endforeach
          </select>
          <button type="button" id="bulkApply" class="btn btn-sm btn-primary" disabled>{{ __('console/common.confirm') }}</button>
        </div>
        <a href="{{ console_route('order_returns.export') }}?{{ http_build_query(request()->except('page')) }}"
           class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-file-earmark-excel me-1"></i>{{ __('console/common.export') }}
        </a>
      </div>

      @if ($order_returns->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td style="width:36px;"><input type="checkbox" id="checkAll" class="form-check-input"></td>
              <td>{{ __('console/common.id') }}</td>
              <td>{{ __('front/return.number') }}</td>
              <td>{{ __('console/order_return.customer') }}</td>
              <td>{{ __('front/return.product_name') }}</td>
              <td>{{ __('front/return.quantity') }}</td>
              <td>{{ __('console/return_reason.return_reason') }}</td>
              <td>{{ __('front/return.opened') }}</td>
              <td>{{ __('front/return.status') }}</td>
              <td>{{ __('front/return.created_at') }}</td>
              @hookinsert('console.order_returns.index.header.extra')
              <td>{{ __('console/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>

            @foreach($order_returns as $item)
              <tr>
                <td><input type="checkbox" class="form-check-input row-check" value="{{ $item->id }}"></td>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="{{ console_route('order_returns.edit', [$item->id]) }}" class="text-decoration-none fw-medium">
                    {{ $item->number }}
                  </a>
                </td>
                <td>
                  @if ($item->customer)
                    <a href="{{ console_route('customers.edit', $item->customer_id) }}" target="_blank" class="text-decoration-none">{{ $item->customer->name }}</a> <br/>
                    <span class="text-muted small">{{ $item->customer->email }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <span class="text-muted small">{{ __('console/order_return.order_number') }}:</span>
                  <a href="{{ console_route('orders.show', $item->order_id) }}" target="_blank" class="text-decoration-none">{{ $item->order_number }}</a> <br/>
                  <div class="d-flex align-items-center mt-1">
                    @if ($item->product)
                      <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="img-fluid wh-30 rounded border border-1 me-2">
                    @endif
                    <span>{{ sub_string($item->product_name, 50) }}</span>
                  </div>
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->reason_name ?: '-' }}</td>
                <td>
                  <span class="badge bg-{{ $item->opened ? 'warning' : 'light text-dark' }}">{{ $item->opened_format }}</span>
                </td>
                <td><span class="badge bg-{{ $item->status_color }}">{{ $item->status_format }}</span></td>
                <td><span class="text-muted small">{{ $item->created_at }}</span></td>

                @hookinsert('console.order_returns.index.row.extra', $item)

                <td>
                  <a href="{{ console_route('order_returns.edit', [$item->id]) }}"
                     class="btn btn-sm btn-outline-primary">{{ __('console/common.view')}}</a>
                  <form action="{{ console_route('order_returns.destroy', [$item->id]) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('{{ __('console/common.confirm_delete') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('console/common.delete') }}</button>
                  </form>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $order_returns->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection
@push('footer')
  <script>
    $(function() {
      const selectedText = '{{ __('console/order_return.selected_count', ['count' => '__COUNT__']) }}';

      function selectedIds() {
        return $('.row-check:checked').map(function() {
          return $(this).val();
        }).get();
      }

      function refreshState() {
        const ids = selectedIds();
        $('#selectedCount').text(selectedText.replace('__COUNT__', ids.length));
        $('#bulkApply').prop('disabled', ids.length === 0 || !$('#bulkStatus').val());
      }

      $('#checkAll').on('change', function() {
        $('.row-check').prop('checked', $(this).is(':checked'));
        refreshState();
      });

      $(document).on('change', '.row-check', function() {
        $('#checkAll').prop('checked', $('.row-check:checked').length === $('.row-check').length);
        refreshState();
      });

      $('#bulkStatus').on('change', refreshState);

      $('#bulkApply').on('click', function() {
        const ids = selectedIds();
        const status = $('#bulkStatus').val();
        if (!ids.length || !status) {
          return;
        }
        if (!window.confirm('{{ __('console/order_return.bulk_confirm') }}')) {
          return;
        }
        const $btn = $(this).prop('disabled', true);
        axios.put('{{ console_route('order_returns.bulk.status') }}', {
          ids: ids,
          status: status,
        }).then(function(res) {
          inno.msg(res.message || '{{ __('console/common.updated_success') }}');
          window.location.reload();
        }).catch(function(err) {
          const msg = err && err.response && err.response.data ? err.response.data.message : '';
          inno.msg(msg || '{{ __('console/common.operation_failed') }}');
          $btn.prop('disabled', false);
        });
      });
    });
  </script>
@endpush
