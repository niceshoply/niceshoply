@extends('console::layouts.app')
@section('body-class', 'page-coupon')

@section('title', __('console/coupon.coupons'))

@section('page-title-right')
<a href="{{ console_route('coupons.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i>
  {{ __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('coupons.index')" />

    @if ($coupons->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/coupon.code') }}</td>
            <td>{{ __('console/coupon.type') }}</td>
            <td>{{ __('console/coupon.value') }}</td>
            <td>{{ __('console/coupon.min_amount') }}</td>
            <td>{{ __('console/coupon.used') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($coupons as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td><code>{{ $item->code }}</code></td>
            <td>{{ __('console/coupon.type_'.$item->type) }}</td>
            <td>{{ $item->type === 'percent' ? $item->value.'%' : ($item->type === 'free_shipping' ? '-' : currency_format($item->value)) }}</td>
            <td>{{ $item->min_amount > 0 ? currency_format($item->min_amount) : '-' }}</td>
            <td>{{ $item->used_count }}{{ $item->total_limit ? ' / '.$item->total_limit : '' }}</td>
            <td>
              @include('console::shared.list_switch', ['value' => $item->active, 'url' => console_route('coupons.active', $item->id)])
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ console_route('coupons.usages', [$item->id]) }}">
                  <el-button size="small" plain>{{ __('console/coupon.usages') }}</el-button>
                </a>
                <a href="{{ console_route('coupons.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit') }}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('coupons.destroy', [$item->id]) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <el-button size="small" type="danger" plain @click="open({{ $item->id }})">{{ __('console/common.delete') }}</el-button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $coupons->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection

@push('footer')
<script>
  const { createApp, ref } = Vue;
  const { ElMessageBox } = ElementPlus;
  const app = createApp({
    setup() {
      const deleteForm = ref(null);
      const open = (itemId) => {
        ElMessageBox.confirm(
          '{{ __("common/base.hint_delete") }}',
          '{{ __("common/base.cancel") }}',
          {
            confirmButtonText: '{{ __("common/base.confirm") }}',
            cancelButtonText: '{{ __("common/base.cancel") }}',
            type: 'warning',
          }
        ).then(() => {
          deleteForm.value.action = urls.console_base + '/coupons/' + itemId;
          deleteForm.value.submit();
        }).catch(() => {});
      };
      return { open, deleteForm };
    }
  });
  app.use(ElementPlus);
  app.mount('#app');
</script>
@endpush
