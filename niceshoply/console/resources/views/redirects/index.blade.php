@extends('console::layouts.app')
@section('body-class', 'page-redirect')

@section('title', __('console/redirect.title'))

@section('page-title-right')
<a href="{{ console_route('redirects.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i>
  {{ __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('redirects.index')" />

    @if ($redirects->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/redirect.source_path') }}</td>
            <td>{{ __('console/redirect.target_path') }}</td>
            <td>{{ __('console/redirect.status_code') }}</td>
            <td>{{ __('console/redirect.hits') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($redirects as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td><code>{{ $item->source_path }}</code></td>
            <td><code>{{ $item->target_path }}</code></td>
            <td>{{ $item->status_code }}</td>
            <td>{{ $item->hits }}</td>
            <td>
              @if($item->active)
              <span class="badge bg-success">{{ __('console/common.yes') }}</span>
              @else
              <span class="badge bg-secondary">{{ __('console/common.no') }}</span>
              @endif
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ console_route('redirects.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit') }}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('redirects.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
    {{ $redirects->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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
          deleteForm.value.action = urls.console_base + '/redirects/' + itemId;
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
