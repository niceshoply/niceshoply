@extends('console::layouts.app')
@section('body-class', 'page-tag')

@section('title', __('console/menu.tags'))

@section('page-title-right')
<a href="{{ console_route('tags.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i>
  {{ __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('tags.index')" />

    @if ($tags->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id')}}</td>
            <td>{{ __('console/tag.name') }}</td>
            <td>{{ __('console/common.slug') }}</td>
            <td>{{ __('console/common.position') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($tags as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->translation->name ?? '' }}</td>
            <td>{{ $item->slug }}</td>
            <td>{{ $item->position }}</td>
            <td>
              @include('console::shared.list_switch', ['value' => $item->active, 'url' => console_route('tags.active',
              $item->id)])</td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ console_route('tags.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit')}}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('tags.destroy', [$item->id]) }}" method="POST"
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
    {{ $tags->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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
      const deleturl =urls.console_base+'/tags/'+index;
      deleteForm.value.action=deleturl;
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
