@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.customer_groups'))
@section('page-title-right')
<a href="{{ console_route('customer_groups.create') }}" class="btn btn-primary">
  <i class="bi bi-plus-square"></i> {{ __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('customer_groups.index')" />

    @if ($groups->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/common.name') }}</td>
            <td>{{ __('console/customer.level') }}</td>
            <td>{{ __('console/customer.mini_cost') }}</td>
            <td>{{ __('console/customer.discount_rate') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($groups as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->fallbackName() }}</td>
            <td>{{ $item->level }}</td>
            <td>{{ currency_format($item->mini_cost, system_setting('currency')) }}</td>
            <td>{{ $item->discount_rate }}</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ console_route('customer_groups.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit')}}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('customer_groups.destroy', [$item->id]) }}" method="POST"
                  class="d-inline">
                  @csrf
                  @method('DELETE')
                  <el-button size="small" type="danger" plain @click="open({{$item->id}})">{{
                    __('console/common.delete')}}</el-button>
                </form>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $groups->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection

@push('footer')
<script>
  const { createApp, ref } = Vue;
    const { ElMessageBox, ElMessage } = ElementPlus;

    const app = createApp({
    setup() {
    const deleteForm = ref(null);

     const open = (itemId) => {
    ElMessageBox.confirm(
       '{{ __("common/base.hint_delete") }}',
       '{{ __("common/base.cancel") }}',
       {
       confirmButtonText: '{{ __("common/base.confirm")}}',
       cancelButtonText: '{{ __("common/base.cancel")}}',
       type: 'warning',
       }
     )
     .then(() => {
    const deletUrl =urls.console_base +'/customer_groups/'+ itemId;
    deleteForm.value.action = deletUrl;
    deleteForm.value.submit();
    })
     .catch(() => {
     });
     };

     return { open, deleteForm };
     }
    });

     app.use(ElementPlus);
     app.mount('#app');
</script>
@endpush
