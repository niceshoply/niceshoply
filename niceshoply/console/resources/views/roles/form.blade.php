@extends('console::layouts.app')

@section('title', __('console/role.roles'))

<x-console::form.right-btns/>

@section('content')

  <div class="card h-min-600">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('console/role.roles') }}</h5>
    </div>
    <div class="card-body">
      <form class="needs-validation" id="app-form" novalidate
            action="{{ $role->id ? console_route('roles.update', [$role->id]) : console_route('roles.store') }}"
            method="POST">
        @csrf
        @method($role->id ? 'PUT' : 'POST')

        <div class="wp-400">
          <x-common-form-input title="{{ __('console/role.name') }}" name="name" value="{{ old('name', $role->name) }}"
                               required placeholder="{{ __('console/role.name') }}"/>
        </div>

        <div class="wp-900">
          <x-console::form.row title="{{ __('console/role.permissions') }}">
            <div class="roles-wrap">
              <table class="table table-bordered">
                <thead>
                <tr>
                  <th class="bg-light">
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-select-all">
                      {{ __('console/role.select_all') }}
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-uncheck">
                      {{ __('console/role.unselect_all') }}
                    </button>
                  </th>
                </tr>
                </thead>
                <tbody>
                @foreach ($permissions as $item)
                  <tr>
                    <td>
                      <span class="me-2">@if($item['is_plugin']) <i class="bi bi-house-add"></i> @else <i class="bi bi-house"></i> @endif {{ $item['label'] }}</span>
                      [<span class="text-secondary cursor-pointer select-list">{{ __('console/role.select_all') }}</span>
                      /
                      <span class="text-secondary cursor-pointer cancel-list">{{ __('console/role.unselect_all') }}</span>]
                    </td>
                  </tr>
                  <tr>
                    <td>
                      <div class="d-flex flex-wrap">
                        @foreach ($item['permissions'] as $child)
                          <div class="form-check me-3 mb-2" data-id="{{ $child['route_slug'] }}">
                            <label class="form-check-label">
                              <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $child['route_slug'] }}"
                                     @if ($child['selected']) checked @endif>{{ $child['label'] }}
                            </label>
                          </div>
                        @endforeach
                      </div>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
          </x-console::form.row>
        </div>

        <button type="submit" class="d-none"></button>
      </form>
    </div>
  </div>
@endsection

@push('footer')
  <script>
    $(function () {
      $('.btn-select-all').click(function () {
        $(this).closest('table').find('input[type="checkbox"]').prop('checked', true);
      });

      $('.btn-uncheck').click(function () {
        $(this).closest('table').find('input[type="checkbox"]').prop('checked', false);
      });

      $('.select-list').click(function () {
        $(this).closest('tr').next().find('input[type="checkbox"]').prop('checked', true);
      });

      $('.cancel-list').click(function () {
        $(this).closest('tr').next().find('input[type="checkbox"]').prop('checked', false);
      });
    });
  </script>
@endpush