@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.catalogs'))
@section('page-title-right')
  <a href="{{ console_route('catalogs.create') }}" class="btn btn-primary"><i
        class="bi bi-plus-square"></i> {{ __('console/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('catalogs.index')" />

      @if ($catalogs->count())
      <div class="table-responsive">
        <table class="table align-middle">
        <thead>
        <tr>
        <td>{{ __('console/common.id')}}</td>
        <td>{{ __('console/catalog.title') }}</td>
        <td>{{ __('console/catalog.parent') }}</td>
        <td>{{ __('console/common.slug') }}</td>
        <td>{{ __('console/common.position') }}</td>
        <td>{{ __('console/common.active') }}</td>
        <td>{{ __('console/common.actions') }}</td>
        </tr>
        </thead>
        <tbody>
        @foreach($catalogs as $item)
        <tr>
        <td>{{ $item->id }}</td>
        <td>{{ $item->fallbackName('title') }}</td>
        <td>{{ $item->parent ? $item->parent->fallbackName('title') : '-' }}</td>
        <td>{{ $item->slug }}</td>
        <td>{{ $item->position }}</td>
        <td>@include('console::shared.list_switch', ['value' => $item->active, 'url' => console_route('catalogs.active', $item->id)])</td>
        <td>
         <div class="d-flex gap-1">
        <a href="{{ console_route('catalogs.edit', [$item->id]) }}">
        <el-button size="small" plain type="primary">{{ __('console/common.edit')}}</el-button>
        </a>
        <form ref="deleteForm" action="{{ console_route('catalogs.destroy', [$item->id]) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <el-button size="small" type="danger" plain @click="open({{$item->id}})">{{ __('console/common.delete')}}</el-button>
        </form>
        </div>
        </td>
        </tr>
      @endforeach
        </tbody>
        </table>
      </div>
      {{ $catalogs->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
        <x-common-no-data/>
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
       const deleteUrl = urls.console_base + '/catalogs/' + itemId;
       deleteForm.value.action = deleteUrl;
       deleteForm.value.submit();
      })
      .catch(() => {
      // 取消删除
      });
    };

    return { open, deleteForm };
    }
    });

    app.use(ElementPlus);
    app.mount('#app');
    </script>
@endpush
