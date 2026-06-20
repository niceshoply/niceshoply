@extends('console::layouts.app')

@section('title', __('console/menu.admins'))

<x-console::form.right-btns/>

@section('content')
  <div class="card h-min-600">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('console/menu.admins') }}</h5>
    </div>
    <div class="card-body">
      <form class="needs-validation" novalidate id="app-form"
            action="{{ $admin->id ? console_route('admins.update', [$admin->id]) : console_route('admins.store') }}"
            method="POST">
        @csrf
        @method($admin->id ? 'PUT' : 'POST')

        <x-common-form-input title="{{ __('console/admin.name') }}" name="name" value="{{ old('name', $admin->name) }}" required />

        <x-common-form-input title="{{ __('console/admin.password') }}" name="password" value="{{ old('email') }}" />

        <x-common-form-input title="{{ __('console/admin.email') }}" name="email" value="{{ old('email', $admin->email) }}" required/>

        <x-common-form-select title="{{ __('console/admin.system_language') }}" name="locale" key="code" label="name" :options="locales()->toArray()" :empty-option="false" :value="old('locale', $admin->locale)" required/>
        
        @hookinsert('console.admins.form.language.after') 

        <x-console::form.row title="{{ __('console/admin.roles') }}" :required="true">
          <div class="form-control px-0 border-0 d-flex flex-wrap">
            @foreach ($roles as $item)
              <div class="form-check me-2">
                <label class="form-check-label">
                  <input class="form-check-input" type="checkbox" value="{{ $item->id }}" name="roles[]"
                      {{ in_array($item->id, $admin->roles->pluck('id')->toArray()) ? 'checked' : '' }}>
                  {{ $item->name }}
                </label>
              </div>
            @endforeach
          </div>
        </x-console::form.row>

        <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active" :value="old('active', $page->active ?? true)"
                                    placeholder="{{ __('console/common.whether_enable') }}"/>


        <button type="submit" class="d-none"></button>
      </form>
    </div>
  </div>
@endsection

@push('footer')
  <script></script>
@endpush