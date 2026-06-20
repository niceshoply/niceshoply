@extends('console::layouts.app')
@section('body-class', 'page-attribute-management')

@section('title', __('console/menu.attributes'))
@section('page-title-right')
<a href="{{ console_route('attributes.create') }}" class="btn btn-primary">
  <i class="bi bi-plus-square"></i> {{ __('console/common.create') }}
</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">
    <!-- Navigation links -->
    <ul class="nav nav-tabs mb-3">
      <li class="nav-item">
        <a class="nav-link active" href="{{ console_route('attributes.index') }}">
          <i class="bi bi-tags"></i> {{ __('console/menu.attributes') }}
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ console_route('attribute_groups.index') }}">
          <i class="bi bi-collection"></i> {{ __('console/menu.attribute_groups') }}
        </a>
      </li>
    </ul>

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('attributes.index')" />

    @if ($attributes->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id')}}</td>
            <td>{{ __('console/common.name')}}</td>
            <td>{{ __('console/menu.attribute_groups')}}</td>
            <td>{{ __('console/common.position')}}</td>
            <td>{{ __('console/common.created_at')}}</td>
            <td>{{ __('console/common.actions')}}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($attributes as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->fallbackName() }}</td>
            <td>{{ $item->group ? $item->group->fallbackName() : '' }}</td>
            <td>{{ $item->position }}</td>
            <td>{{ $item->created_at }}</td>
            <td>
              <div class="d-flex gap-2">
                <div>
                  <a href="{{ console_route('attributes.edit', [$item->id]) }}">
                    <el-button size="small" plain type="primary">{{
      __('console/common.edit')}}</el-button>
                  </a>
                </div>
                <div>
                  <form ref="deleteForm" action="{{ console_route('attributes.destroy', [$item->id]) }}" method="POST"
                    class="d-inline">
                    @csrf
                    @method('DELETE')
                    <el-button size="small" type="danger" plain @click="open({{$item->id}})">{{ __('console/common.delete')}}</el-button>
                  </form>
                </div>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $attributes->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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
       const deleteUrl=urls.console_base+'/attributes/' +index;
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
