@extends('console::layouts.app')
@section('body-class', 'page-member-level')

@section('title', __('console/member.member_levels'))

@section('page-title-right')
<a href="{{ console_route('member_levels.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i>
  {{ __('console/common.create') }}</a>
@endsection

@section('content')
<div class="card h-min-600" id="app">
  <div class="card-body">

    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('member_levels.index')" />

    @if ($memberLevels->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/member.name') }}</td>
            <td>{{ __('console/member.threshold_type') }}</td>
            <td>{{ __('console/member.threshold_value') }}</td>
            <td>{{ __('console/member.discount_percent') }}</td>
            <td>{{ __('console/member.free_shipping') }}</td>
            <td>{{ __('console/member.priority') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($memberLevels as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->label }}</td>
            <td>{{ __('console/member.threshold_'.$item->threshold_type) }}</td>
            <td>{{ $item->threshold_value }}</td>
            <td>{{ $item->discount_percent }}%</td>
            <td>{{ $item->free_shipping ? __('console/common.yes') : __('console/common.no') }}</td>
            <td>{{ $item->priority }}</td>
            <td>
              @include('console::shared.list_switch', ['value' => $item->active, 'url' => console_route('member_levels.active', $item->id)])
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="{{ console_route('member_levels.edit', [$item->id]) }}">
                  <el-button size="small" plain type="primary">{{ __('console/common.edit') }}</el-button>
                </a>
                <form ref="deleteForm" action="{{ console_route('member_levels.destroy', [$item->id]) }}" method="POST" class="d-inline">
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
    {{ $memberLevels->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
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
          deleteForm.value.action = urls.console_base + '/member_levels/' + itemId;
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
