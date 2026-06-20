@extends('console::layouts.app')
@section('body-class', 'page-customer')

@section('title', __('console/menu.customers'))
@section('page-title-right')
<a href="{{ console_route('customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('customers.index')" />

    @if ($customers->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id')}}</td>
            <td>{{ __('console/customer.customer_info') }}</td>
            @hookinsert('console.customer.index.thead.bottom')
            <td>{{ __('console/customer.from') }}</td>
            <td>{{ __('console/customer.group') }}</td>
            <td>{{ __('console/customer.locale') }}</td>
            @hookinsert('console.product.index.thead.bottom')
            <td>{{ __('console/common.created_at') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($customers as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td class="customer-info-cell">
              <div class="customer-info-wrapper">
                <div class="avatar-wrapper">
                  <img src="{{ image_resize($item->avatar, 40, 40) }}" 
                       alt="{{ $item->name }}">
                </div>
                <div class="customer-details">
                  <div class="customer-name">{{ $item->name }}</div>
                  <div class="customer-email">{{ $item->email }}</div>
                </div>
              </div>
            </td>
            @hookinsert('console.customer.index.tbody.bottom', $item)
            <td>{{ $item->from_display }}</td>
            <td>{{ $item->customerGroup->translation->name ?? '-' }}</td> 
            <td>{{ $item->locale }}</td>
            @hookinsert('console.product.index.tbody.bottom', $item)
            <td>{{ $item->created_at }}</td>
            <td>
              @include('console::shared.list_switch', ['value' => $item->active, 'url' => console_route('customers.active',
              $item)])
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ console_route('customers.login', [$item->id]) }}" target="_blank">
                  <el-button size="small" plain type="primary">{{ __('console/customer.login_frontend')}}</el-button>
                </a>
                <a href="{{ console_route('customers.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit')}}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('customers.destroy', [$item->id]) }}" method="POST"
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
    {{ $customers->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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

     const open = (index) => {
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
      const deleteUrl = urls.console_base+'/customers/'+index;
      deleteForm.value.action=deleteUrl;
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
