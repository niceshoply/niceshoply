@extends('console::layouts.app')

@section('title', __('console/menu.warehouses'))

<x-console::form.right-btns />

@section('content')
<div class="card h-min-600">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/menu.warehouses') }}</h5>
  </div>
  <div class="card-body">
    <form class="needs-validation" novalidate id="app-form"
      action="{{ $warehouse->id ? console_route('warehouses.update', [$warehouse->id]) : console_route('warehouses.store') }}"
      method="POST">
      @csrf
      @method($warehouse->id ? 'PUT' : 'POST')

      <x-common-form-input title="{{ __('console/warehouse.code') }}" name="code" value="{{ old('code', $warehouse->code) }}" required placeholder="{{ __('console/warehouse.code') }}" />
      <x-common-form-input title="{{ __('console/warehouse.name') }}" name="name" value="{{ old('name', $warehouse->name) }}" required placeholder="{{ __('console/warehouse.name') }}" />
      <x-common-form-input title="{{ __('console/warehouse.description') }}" name="description" value="{{ old('description', $warehouse->description) }}" placeholder="{{ __('console/warehouse.description') }}" />
      <x-common-form-input title="{{ __('console/warehouse.contact_name') }}" name="contact_name" value="{{ old('contact_name', $warehouse->contact_name) }}" placeholder="{{ __('console/warehouse.contact_name') }}" />
      <x-common-form-input title="{{ __('console/warehouse.contact_phone') }}" name="contact_phone" value="{{ old('contact_phone', $warehouse->contact_phone) }}" placeholder="{{ __('console/warehouse.contact_phone') }}" />

      <x-console::form.row title="{{ __('console/warehouse.country') }}">
        <select class="form-select me-3" name="country_id" id="warehouse-country">
          <option value="">{{ __('console/common.please_choose') }}</option>
        </select>
      </x-console::form.row>

      <x-console::form.row title="{{ __('console/warehouse.state') }}">
        <select class="form-select me-3" name="state_id" id="warehouse-state" disabled>
          <option value="">{{ __('console/common.please_choose') }}</option>
        </select>
      </x-console::form.row>

      <x-common-form-input title="{{ __('console/warehouse.city') }}" name="city" value="{{ old('city', $warehouse->city) }}" placeholder="{{ __('console/warehouse.city') }}" />
      <x-common-form-input title="{{ __('console/warehouse.address_1') }}" name="address_1" value="{{ old('address_1', $warehouse->address_1) }}" placeholder="{{ __('console/warehouse.address_1') }}" />
      <x-common-form-input title="{{ __('console/warehouse.address_2') }}" name="address_2" value="{{ old('address_2', $warehouse->address_2) }}" placeholder="{{ __('console/warehouse.address_2') }}" />
      <x-common-form-input title="{{ __('console/warehouse.zipcode') }}" name="zipcode" value="{{ old('zipcode', $warehouse->zipcode) }}" placeholder="{{ __('console/warehouse.zipcode') }}" />
      <x-common-form-input title="{{ __('console/warehouse.priority') }}" name="priority" value="{{ old('priority', $warehouse->priority ?? 0) }}" placeholder="{{ __('console/warehouse.priority') }}" />
      <x-common-form-switch-radio title="{{ __('console/warehouse.is_default') }}" name="is_default" :value="old('is_default', $warehouse->is_default ?? false)" />
      <x-common-form-switch-radio title="{{ __('console/common.whether_enable') }}" name="active" :value="old('active', $warehouse->active ?? true)" />

      {{-- Service Areas --}}
      <div class="mt-4 mb-3">
        <label class="form-label fw-bold">{{ __('console/warehouse.service_areas') }}</label>
        <div class="form-text text-muted mb-2">{{ __('console/warehouse.service_area_hint') }}</div>
        <table class="table table-bordered table-sm" id="service-areas-table">
          <thead>
            <tr>
              <th>{{ __('console/warehouse.country') }}</th>
              <th>{{ __('console/warehouse.state') }}</th>
              <th width="80" class="text-end">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-sa">
                  <i class="bi bi-plus"></i>
                </button>
              </th>
            </tr>
          </thead>
          <tbody id="sa-tbody"></tbody>
        </table>
      </div>

      <button type="submit" class="d-none"></button>
    </form>
  </div>
</div>
@endsection

@push('footer')
<script>
  const currentCountryId = @json(old('country_id', $warehouse->country_id) ?? '');
  const currentStateId = @json(old('state_id', $warehouse->state_id) ?? '');
  const existingServiceAreas = @json($warehouse->serviceAreas ?? []);
  var countriesCache = [];
  var saIndex = 0;

  loadCountries();

  $('#warehouse-country').on('change', function() {
    loadStates($(this).val());
  });

  function loadCountries() {
    axios.get('{{ front_route('countries.index') }}').then(function(res) {
      countriesCache = res.data;
      var select = $('#warehouse-country');
      select.find('option:not(:first)').remove();
      countriesCache.forEach(function(item) {
        select.append('<option value="' + item.id + '"' + (item.id == currentCountryId ? ' selected' : '') + '>' + item.name + ' (' + item.code + ')</option>');
      });
      if (currentCountryId) {
        loadStates(currentCountryId);
      }
      // Init existing service areas
      existingServiceAreas.forEach(function(area) {
        addServiceAreaRow(area.country_id, area.state_id);
      });
    });
  }

  function loadStates(countryId) {
    var select = $('#warehouse-state');
    select.empty().append('<option value="">{{ __('console/common.please_choose') }}</option>');
    if (!countryId) {
      select.prop('disabled', true);
      return;
    }
    axios.get('{{ front_route('countries.index') }}/' + countryId).then(function(res) {
      var states = res.data;
      select.prop('disabled', false);
      states.forEach(function(item) {
        select.append('<option value="' + item.id + '"' + (item.id == currentStateId ? ' selected' : '') + '>' + item.name + '</option>');
      });
    });
  }

  // Service Areas
  function addServiceAreaRow(countryId, stateId) {
    var idx = saIndex++;
    var countryOptions = '<option value="">{{ __('console/common.please_choose') }}</option>';
    countriesCache.forEach(function(c) {
      countryOptions += '<option value="' + c.id + '"' + (c.id == countryId ? ' selected' : '') + '>' + c.name + ' (' + c.code + ')</option>';
    });
    var row = '<tr data-idx="' + idx + '">' +
      '<td><select class="form-select form-select-sm sa-country" name="service_areas[' + idx + '][country_id]">' + countryOptions + '</select></td>' +
      '<td><select class="form-select form-select-sm sa-state" name="service_areas[' + idx + '][state_id]" data-state="' + (stateId || 0) + '">' +
      '<option value="0">{{ __('console/warehouse.all_states') }}</option></select></td>' +
      '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-sa"><i class="bi bi-trash"></i></button></td></tr>';
    $('#sa-tbody').append(row);
    if (countryId) {
      loadSaStates($('#sa-tbody tr[data-idx=' + idx + ']'), countryId, stateId || 0);
    }
  }

  function loadSaStates($row, countryId, selectedStateId) {
    var select = $row.find('.sa-state');
    select.empty().append('<option value="0">{{ __('console/warehouse.all_states') }}</option>');
    if (!countryId) return;
    axios.get('{{ front_route('countries.index') }}/' + countryId).then(function(res) {
      res.data.forEach(function(item) {
        select.append('<option value="' + item.id + '"' + (item.id == selectedStateId ? ' selected' : '') + '>' + item.name + '</option>');
      });
    });
  }

  $('#btn-add-sa').on('click', function() {
    addServiceAreaRow('', 0);
  });

  $(document).on('change', '.sa-country', function() {
    var $row = $(this).closest('tr');
    loadSaStates($row, $(this).val(), 0);
  });

  $(document).on('click', '.btn-remove-sa', function() {
    $(this).closest('tr').remove();
  });
</script>
@endpush
