@extends('console::layouts.app')

@section('title', __('console/menu.plugin_settings'))

@section('page-title-right')
  <button type="submit" form="settings-form" class="btn btn-primary">{{ __('console/common.btn_save') }}</button>
@endsection

@section('content')
  <div class="card h-min-600">
    <div class="card-body">
      <div class="row">
        <form id="settings-form" action="{{ console_route('plugins.settings.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-6">
              <h5 class="mb-4">{{ __('console/plugin.display_settings') }}</h5>

              <div class="mb-4">
                <label class="form-label">{{ __('console/plugin.display_mode') }}</label>
                <select name="plugin.display_mode" class="form-select">
                  <option value="card" @selected(system_setting('plugin.display_mode', 'card' )==='card'
                                    )>{{ __('console/plugin.display_mode_card') }}</option>
                  <option value="grid" @selected(system_setting('plugin.display_mode', 'card' )==='grid'
                                    )>{{ __('console/plugin.display_mode_grid') }}</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <h5 class="mb-4">{{ __('console/plugin.system_settings') }}</h5>

              <div class="mb-3">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="plugin.auto_update" value="1"
                         id="auto_update" @checked(system_setting('plugin.auto_update', false))>
                  <label class="form-check-label" for="auto_update">
                    {{ __('console/plugin.enable_auto_update') }}
                  </label>
                </div>
              </div>

              <div class="mb-3">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="plugin.developer_mode" value="1"
                         id="developer_mode" @checked(system_setting('plugin.developer_mode', false))>
                  <label class="form-check-label" for="developer_mode">
                    {{ __('console/plugin.enable_developer_mode') }}
                  </label>
                </div>
              </div>

              <div class="mb-3">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="plugin.enable_logging" value="1"
                         id="enable_logging" @checked(system_setting('plugin.enable_logging', true))>
                  <label class="form-check-label" for="enable_logging">
                    {{ __('console/plugin.enable_logging') }}
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-4">
            <div class="col-md-6">
              <h5 class="mb-4">{{ __('console/plugin.marketplace_settings') }}</h5>

              <div class="mb-3">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="plugin.enable_marketplace"
                         value="1" id="enable_marketplace"
                    @checked(system_setting('plugin.enable_marketplace', true))>
                  <label class="form-check-label" for="enable_marketplace">
                    {{ __('console/plugin.enable_marketplace') }}
                  </label>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">{{ __('console/plugin.marketplace_url') }}</label>
                <input type="url" class="form-control" name="plugin.marketplace_url"
                       value="{{ system_setting('plugin.marketplace_url', 'https://marketplace.niceshoply.com') }}">
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
