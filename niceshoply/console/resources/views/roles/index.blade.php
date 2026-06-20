@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.roles'))
@section('page-title-right')
<a href="{{ console_route('roles.create') }}" class="btn btn-primary">
  <i class="bi bi-plus-square"></i> {{ __('console/common.create') }}
</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    @if ($roles->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>{{ __('console/common.id') }}</th>
            <th>{{ __('console/common.name') }}</th>
            <th>{{ __('console/common.created_at') }}</th>
            <th>{{ __('console/common.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($roles as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->created_at }}</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ console_route('roles.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit')}}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('roles.destroy', [$item->id]) }}" method="POST"
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
    {{ $roles->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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
    const deletUrl =urls.console_base +'/roles/'+ itemId;
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