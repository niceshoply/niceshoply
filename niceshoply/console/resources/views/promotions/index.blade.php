@extends('console::layouts.app')
@section('body-class', 'page-promotion')

@section('title', __('console/promotion.promotions'))

@section('page-title-right')
<a href="{{ console_route('promotions.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i>
  {{ __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('promotions.index')" />

    @if ($promotions->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/promotion.name') }}</td>
            <td>{{ __('console/promotion.scope') }}</td>
            <td>{{ __('console/promotion.action_type') }}</td>
            <td>{{ __('console/promotion.priority') }}</td>
            <td>{{ __('console/promotion.used') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($promotions as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ __('console/promotion.scope_'.$item->scope) }}</td>
            <td>{{ __('console/promotion.action_'.$item->action_type) }}</td>
            <td>{{ $item->priority }}</td>
            <td>{{ $item->used_count }}{{ $item->usage_limit ? ' / '.$item->usage_limit : '' }}</td>
            <td>
              @include('console::shared.list_switch', ['value' => $item->active, 'url' => console_route('promotions.active', $item->id)])
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ console_route('promotions.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit') }}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('promotions.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
    {{ $promotions->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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
        ElMessageBox.confirm(
          '{{ __("common/base.hint_delete") }}',
          '{{ __("common/base.cancel") }}',
          {
            confirmButtonText: '{{ __("common/base.confirm") }}',
            cancelButtonText: '{{ __("common/base.cancel") }}',
            type: 'warning',
          }
        ).then(() => {
          deleteForm.value.action = urls.console_base + '/promotions/' + itemId;
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
