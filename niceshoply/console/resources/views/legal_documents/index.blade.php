@extends('console::layouts.app')
@section('body-class', 'page-legal-document')
@section('title', __('console/legal.title'))
@section('page-title-right')
<a href="{{ console_route('legal_documents.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{ __('console/common.create') }}</a>
@endsection
@section('content')
<div class="card h-min-600">
  <div class="card-body">
    <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('legal_documents.index')" />
    @if ($documents->count())
    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <td>{{ __('console/common.id') }}</td>
            <td>{{ __('console/legal.type') }}</td>
            <td>{{ __('console/legal.version') }}</td>
            <td>{{ __('console/common.active') }}</td>
            <td>{{ __('console/common.actions') }}</td>
          </tr>
        </thead>
        <tbody>
          @foreach($documents as $item)
          <tr>
            <td>{{ $item->id }}</td>
            <td>{{ __('console/legal.type_'.$item->type) }}</td>
            <td>{{ $item->version }}</td>
            <td>{{ $item->active ? __('console/common.yes') : __('console/common.no') }}</td>
            <td>
              <a href="{{ console_route('legal_documents.edit', [$item->id]) }}" class="btn btn-sm btn-outline-primary">{{ __('console/common.edit') }}</a>
              <form action="{{ console_route('legal_documents.destroy', [$item->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('common/base.hint_delete') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('console/common.delete') }}</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    {{ $documents->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
