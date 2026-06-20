@extends('console::layouts.app')
@section('body-class', 'page-page')

@section('title', __('console/menu.pages'))
@section('page-title-right')
<a href="{{ console_route('pages.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('pages.index')" />

    @if ($pages->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id')}}</td>
            <td>{{ __('console/article.title') }}</td>
            <td>{{ __('console/common.slug') }}</td>
            <td>{{ __('console/common.viewed') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($pages as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td><a href="{{ $item->url }}" target="_blank" class="text-decoration-none">{{ $item->fallbackName('title') }}</a></td>
            <td>{{ $item->slug }}</td>
            <td>{{ $item->viewed }}</td>
            <td>@include('console::shared.list_switch', ['value' => $item->active, 'url' => console_route('pages.active',
              $item->id)])</td>
            <td>
              <div class="d-flex gap-1">
                @hookinsert('console.page.list.table.row.actions.before', $item)
                <a href="{{ console_route('pages.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit')}}</el-button>
                </a>
                @hookinsert('console.page.list.table.row.actions.after', $item)
                <form ref="deleteForm" action="{{ console_route('pages.destroy', [$item->id]) }}" method="POST"
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
    {{ $pages->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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
      const deleteUrl =urls.console_base +'/pages/'+index;
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
