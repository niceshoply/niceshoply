@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.articles'))
@section('page-title-right')
  <a href="{{ console_route('articles.create') }}" class="btn btn-primary"><i
      class="bi bi-plus-square"></i> {{ __('console/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

      <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('articles.index')"/>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
          <tr>
            <td>{{ __('console/common.id')}}</td>
            <td>{{ __('console/article.image') }}</td>
            <td>{{ __('console/article.title') }}</td>
            <td class="d-none d-md-table-cell">{{ __('console/article.catalog') }}</td>
            <td class="d-none d-md-table-cell">{{ __('console/common.slug') }}</td>
            <td>{{ __('console/common.position') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
          </thead>
          @if ($articles->count())
            <tbody>
            @foreach($articles as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td><img src="{{ image_resize($item->image, 30, 30) }}" class="wh-30"></td>
                <td>
                  <a href="{{ $item->url }}" target="_blank" class="text-decoration-none" data-bs-toggle="tooltip" title="{{ $item->fallbackName('title') }}">
                    {{ sub_string($item->fallbackName('title'), 32) }}
                  </a>
                </td>
                <td class="d-none d-md-table-cell">{{ $item->catalog->translation->title ?? '-' }}</td>
                <td class="d-none d-md-table-cell">
                  <a href="{{ $item->url }}" target="_blank" class="text-decoration-none" data-bs-toggle="tooltip" title="{{ $item->slug ?: '-' }}">
                    {{ sub_string($item->slug ?: '-', 32) }}
                  </a>
                </td>
                <td>{{ $item->position }}</td>
                <td>@include('console::shared.list_switch', ['value' => $item->active, 'url' => console_route('articles.active', $item->id)])</td>
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ console_route('articles.edit', [$item->id]) }}">
                      <el-button size="small" plain type="primary">{{ __('console/common.edit')}}</el-button>
                    </a>
                    <form ref="deleteForm" action="{{ console_route('articles.destroy', [$item->id]) }}" method="POST"
                          class="d-inline">
                      @csrf
                      @method('DELETE')
                      <el-button size="small" type="danger" plain
                                 @click="open({{$item->id}})">{{ __('console/common.delete')}}</el-button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          @else
            <tbody>
            <tr>
              <td colspan="8">
                <x-common-no-data/>
              </td>
            </tr>
            </tbody>
          @endif
        </table>
      </div>
      {{ $articles->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    </div>
  </div>
@endsection

@push('footer')
  <script>
    const {createApp, ref} = Vue;
    const {ElMessageBox, ElMessage} = ElementPlus;

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
          ).then(() => {
            deleteForm.value.action = urls.console_base + '/articles/' + index;
            deleteForm.value.submit();
          }).catch(() => {
          });
        };

        return {open, deleteForm};
      }
    });

    app.use(ElementPlus);
    app.mount('#app');
  </script>
@endpush
