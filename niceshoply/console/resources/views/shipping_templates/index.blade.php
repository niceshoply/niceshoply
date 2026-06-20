@extends('console::layouts.app')
@section('body-class', 'page-shipping-template')

@section('title', __('console/shipping.templates'))

@section('page-title-right')
<a href="{{ console_route('shipping_templates.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i>
  {{ __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('shipping_templates.index')" />

    @if ($templates->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/shipping.template_name') }}</td>
            <td>{{ __('console/shipping.zone') }}</td>
            <td>{{ __('console/shipping.calc_type') }}</td>
            <td>{{ __('console/shipping.free_threshold') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($templates as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ optional($item->zone)->name ?? __('console/shipping.all_zones') }}</td>
            <td>{{ __('console/shipping.calc_'.$item->calc_type) }}</td>
            <td>{{ $item->free_threshold > 0 ? currency_format($item->free_threshold) : '-' }}</td>
            <td>@include('console::shared.list_switch', ['value' => $item->active, 'url' => console_route('shipping_templates.active', $item->id)])</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ console_route('shipping_templates.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit') }}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('shipping_templates.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
    {{ $templates->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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
        ElMessageBox.confirm('{{ __("common/base.hint_delete") }}', '{{ __("common/base.cancel") }}', {
          confirmButtonText: '{{ __("common/base.confirm") }}',
          cancelButtonText: '{{ __("common/base.cancel") }}',
          type: 'warning',
        }).then(() => {
          deleteForm.value.action = urls.console_base + '/shipping_templates/' + itemId;
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
