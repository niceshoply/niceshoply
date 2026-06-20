@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.warehouses'))
@section('page-title-right')
<a href="{{ console_route('warehouses.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('warehouses.index')" />

    @if ($warehouses->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id')}}</td>
            <td>{{ __('console/warehouse.code') }}</td>
            <td>{{ __('console/warehouse.name') }}</td>
            <td>{{ __('console/warehouse.contact_name') }}</td>
            <td>{{ __('console/warehouse.country') }}</td>
            <td>{{ __('console/warehouse.city') }}</td>
            <td>{{ __('console/warehouse.priority') }}</td>
            <td>{{ __('console/warehouse.is_default') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($warehouses as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->code }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->contact_name }}</td>
            <td>{{ $item->country }}</td>
            <td>{{ $item->city }}</td>
            <td>{{ $item->priority }}</td>
            <td>@if($item->is_default) <span class="badge bg-success">{{ __('common.yes') }}</span> @endif</td>
            <td>@include('console::shared.list_switch', [
              'value' => $item->active,
              'url' => console_route('warehouses.active', $item->id)
              ])</td>
            <td>
              <div class="d-flex gap-2">
                <a href="{{ console_route('warehouses.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit')}}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('warehouses.destroy', [$item->id]) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <el-button size="small" type="danger" plain @click="open({{ $item->id }})">{{ __('console/common.delete')}}</el-button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $warehouses->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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
          confirmButtonText: '{{ __("common/base.confirm")}}',
          cancelButtonText: '{{ __("common/base.cancel")}}',
          type: 'warning',
        }).then(() => {
          const deleteUrl = urls.console_base + '/warehouses/' + itemId;
          deleteForm.value.action = deleteUrl;
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
