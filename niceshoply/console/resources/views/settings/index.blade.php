@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.settings'))
@section('page-eyebrow', __('console/menu.settings'))
@section('page-subtitle', __('console/setting.index_subtitle'))

<x-console::form.right-btns />

@section('content')
<form class="needs-validation" novalidate action="{{ console_route('settings.update') }}" method="POST" id="app-form">
  @csrf
  @method('put')
  <div class="row">
    <div class="col-md-3">
      <div class="card" id="setting-menu">
        <div class="card-header">{{ __('console/menu.settings') }}</div>
        <div class="card-body">
          <ul class="nav flex-column settings-nav">
            <a class="nav-link active" href="#" data-bs-target="#tab-setting-basics">{{ __('console/setting.basic') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-webdata">{{ __('console/setting.website_data') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-image">{{ __('console/setting.image_settings') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-storage">{{ __('console/setting.storage_setting') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-email">{{ __('console/setting.email_setting') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-sms">{{ __('console/setting.sms_setting') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-ai">{{ __('console/setting.ai_setting') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-logistics-information">{{ __('console/setting.express_company') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-warehouse">{{ __('console/warehouse.warehouse_settings') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-notification">{{ __('console/setting.notification_settings') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-tax">{{ __('console/setting.tax_settings') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-points">{{ __('console/setting.points_settings') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-abandoned-cart">{{ __('console/setting.abandoned_cart_settings') }}</a>
            <a class="nav-link" href="#" data-bs-target="#tab-setting-compliance">{{ __('console/setting.compliance_settings') }}</a>
            @hookinsert('console.settings.tab.nav.bottom')
        </ul>
        </div>
      </div>
    </div>
    <div class="col-md-9">
      <div class="card h-min-600">
        <div class="card-header setting-header">{{ __('console/setting.basic') }}</div>
        <div class="card-body">
          <div class="tab-content">
            @include('console::settings._basic_setting')
            @include('console::settings._web_data')
            @include('console::settings._image_setting')
            @include('console::settings._storage_setting')
            @include('console::settings._email_setting')
            @include('console::settings._sms_setting')
            @include('console::settings._ai_setting')
            @include('console::settings._logistics_information')
            @include('console::settings._warehouse_setting')
            @include('console::settings._notification_setting')
            @include('console::settings._tax_setting')
            @include('console::settings._points_setting')
            @include('console::settings._abandoned_cart_setting')
            @include('console::settings._compliance_setting')
            @hookinsert('console.settings.tab.pane.bottom')
          </div>
        </div>
      </div>
    </div>
  </div>

  <button type="submit" class="d-none"></button>
</form>
@endsection

@push('footer')
<script>
  const countryCode = @json(old('country_code', system_setting('country_code')));
  const stateCode = @json(old('state_code', system_setting('state_code')));
  const locales = @json($locales);

  getCountries()
  if (countryCode) {
    getZones(countryCode)
  }

  $('select[name="country_code"]').on('change', function() {
    var countryId = $(this).val();
    getZones(countryId);
  });

  // 获取所有国家数据
  function getCountries() {
    axios.get('{{ front_route('countries.index') }}').then(function(res) {
      var countries = res.data;
      var countrySelect = $('select[name="country_code"]');
      countrySelect.empty();
      countrySelect.append('<option value="">请选择国家</option>');
      countries.forEach(function(country) {
        countrySelect.append('<option value="' + country.code + '"' + (country.code == countryCode ? ' selected' : '') + '>' + country.name + '</option>');
      });
    });
  }

  // 获取对应国家的省份数据 countries/72
  function getZones(countryId) {
    axios.get('{{ front_route('countries.index') }}/' + countryId).then(function(res) {
      var zones = res.data;
      var zoneSelect = $('select[name="state_code"]');
      zoneSelect.prop('disabled', false).empty();
      zoneSelect.append('<option value="">请选择省份</option>');
      zones.forEach(function(zone) {
        zoneSelect.append('<option value="' + zone.code + '"' + (zone.code == stateCode ? ' selected' : '') + '>' + zone.name + '</option>');
      });
    });
  }

  function addSlide(btn) {
    var tbody = $(btn).closest('table').find('tbody');
    var index = tbody.find('tr').length;
    var tr = `
      <tr>
        <td>
          <div class="accordion accordion-sm" id="accordion-slideshow-${index}">
            ${locales.map((locale, locale_index) => `
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button py-2 ${locale_index === 0 ? '' : 'collapsed'}" type="button" data-bs-toggle="collapse" data-bs-target="#data-locale-${index}-${locale.code}" aria-expanded="false" aria-controls="data-locale-${index}-${locale.code}">
                    <div class="wh-20 me-2"><img src="${locale.image}" class="img-fluid"></div>
                    ${locale.name}
                  </button>
                </h2>
                <div id="data-locale-${index}-${locale.code}" class="accordion-collapse collapse ${locale_index === 0 ? 'show' : ''}" data-bs-parent="#accordion-slideshow-${index}">
                  <div class="accordion-body">
                    <div class="is-up-file slideshow-img">
                      <div class="img-upload-item wh-80 position-relative d-flex justify-content-center rounded overflow-hidden align-items-center border border-1 mb-1 me-1">
                        <div class="position-absolute bg-white d-none img-loading"><div class="spinner-border opacity-50"></div></div>
                        <div class="img-info d-flex justify-content-center align-items-center h-100 w-80 cursor-pointer">
                          <i class="bi bi-plus fs-1 text-secondary opacity-75"></i>
                        </div>
                        <input class="d-none" name="slideshow[${index}][image][${locale.code}]" value="">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
        </td>
        <td>
          <input type="text" name="slideshow[${index}][link]" class="form-control">
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">删除</button>
        </td>
      </tr>
    `;
    tbody.append(tr);
  }

  $(document).on('click', '.is-up-file.slideshow-img .img-upload-item', function () {
    const _self = $(this);
    $('#form-upload').remove();
    $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" accept="image/*" name="file" /></form>');
    $('#form-upload input[name=\'file\']').trigger('click');
    $('#form-upload input[name=\'file\']').change(function () {
      let file = $(this).prop('files')[0];
      inno.imgUploadAjax(file, _self, (data) => {
        _self.find('input').val(data.data.value);
        _self.find('.img-info').html('<img src="' + data.data.url + '" class="img-fluid">');
      })
    });
  })
</script>
@endpush
