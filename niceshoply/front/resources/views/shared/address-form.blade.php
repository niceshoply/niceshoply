{{--
================================================================================
【文件说明】
  收货地址表单局部模板 —— 用于用户中心"地址管理"的新增/编辑地址弹窗，
  以及结账页（Checkout）的地址填写区块。
  根据是否已登录（current_customer_id()），自动切换表单布局：
    - 已登录：只显示"姓名"（单行）
    - 未登录（游客结账）：显示"姓名 + 邮箱"（并排两列）

【引用方式】
  @include('shared.address-form')
  ※ 无需传入参数，表单数据填写和提交由前台 JS 动态控制。

【可用变量】
  无需父模板传入变量。

  全局辅助函数：
    current_customer_id()          — 返回当前登录会员的 ID（未登录返回 null/false）
    system_setting('country_code') — 系统默认国家代码，用于初始化国家选择框
    system_setting('state_code')   — 系统默认州/省代码
    front_route('countries.index') — 获取国家列表的 API 路由 URL

  多语言翻译 Key：
    common/address.name        — 姓名字段标签
    common/address.email       — 邮箱字段标签（仅游客模式）
    common/address.address_1   — 地址行1标签（主要地址）
    common/address.address_2   — 地址行2标签（补充地址，非必填）
    common/address.zipcode     — 邮政编码标签
    common/address.city        — 城市标签
    common/address.country     — 国家标签（下拉选择）
    common/address.state       — 州/省标签（下拉选择，联动国家）
    common/address.phone       — 电话标签
    front/common.error_required — 必填校验错误提示文字模板（含 :name 参数）
    front/common.default        — "默认地址"开关标签
    front/common.please_choose  — 下拉框默认空选项文字
    front/common.submit         — 提交按钮文字

【输出内容】
  .address-form 表单，包含（字段布局为 Bootstrap Grid）：
  - 隐藏字段：id（用于编辑时回填地址 ID）
  - 姓名（登录用户）/ 姓名 + 邮箱（游客，并排两列）
  - 地址行1（address_1，必填，全宽）
  - 地址行2（address_2，非必填）+ 邮编（并排）
  - 城市 + 国家（下拉，并排）
  - 州/省（下拉联动，依赖所选国家）+ 电话（并排）
  - 默认地址开关（Switch Toggle）
  - 提交按钮（居中，宽度 50%）

【JS 行为（@push('footer')）】
  依赖全局 JS（inno 对象）：
    inno.validateAndSubmitForm('.address-form', callback)
      — Bootstrap 表单验证，通过后调用 updateAddress(data) 函数
      — updateAddress 函数须在父视图中定义，处理实际的提交逻辑（AJAX 或跳转）

  页面加载时自动执行：
    getCountries()            — 通过 API 获取所有国家，填充"国家"下拉框
                                 并按 system_setting('country_code') 预选默认值
    getZones(countryId)       — 选择国家后联动获取该国的州/省列表，填充"州/省"下拉框
                                 若该国无州/省数据，则禁用州/省下拉并显示 N/A

  全局工具函数（父视图可调用）：
    clearForm()               — 重置表单（清空所有字段值 + 移除 Bootstrap 验证状态）

【自定义建议】
  1. 父视图必须定义 updateAddress(data) 函数来处理表单提交：
       function updateAddress(data) {
           axios.post('/api/addresses', data).then(...);
       }
  2. 编辑已有地址时，需通过 JS 将地址数据回填到表单字段，
     并将 input[name="id"] 设置为该地址的 ID。
  3. 如需在表单中新增字段（如公司名称），在对应 .form-group 区块后追加即可，
     后端 AddressRequest 验证规则也需同步更新。
  4. 国家/州省数据来自 countries.index API，该 API 返回格式为：
       国家：[{ code: 'US', name: 'United States' }, ...]
       州省：[{ code: 'CA', name: 'California' }, ...]
================================================================================
--}}
<form class="needs-validation address-form mb-4" novalidate>
  <input type="hidden" name="id" value="">

  @if(current_customer_id())
    <div class="form-group mb-4">
      <label class="form-label" for="name">
        <span class="text-danger">*</span>
        {{ __('common/address.name') }}
      </label>
      <input type="text" class="form-control" name="name" value="" required
             placeholder="{{ __('common/address.name') }}"/>
      <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' =>
      __('common/address.name')]) }}</span>
    </div>
  @else
    <div class="row gx-2">
      <div class="col-6">
        <div class="form-group mb-4">
          <label class="form-label" for="name">
            <span class="text-danger">*</span>
            {{ __('common/address.name') }}
          </label>
          <input type="text" class="form-control" name="name" value="" required
                 placeholder="{{ __('common/address.name') }}"/>
          <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' =>
          __('common/address.name')]) }}</span>
        </div>
      </div>
      <div class="col-6">
        <div class="form-group mb-4">
          <label class="form-label" for="email">
            <span class="text-danger">*</span>
            {{ __('common/address.email') }}
          </label>
          <input type="text" class="form-control" name="email" value="" required
                 placeholder="{{ __('common/address.email') }}"/>
          <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' =>
          __('common/address.email')]) }}</span>
        </div>
      </div>
    </div>
  @endif

  <div class="form-group mb-4">
    <label class="form-label" for="email">
      <span class="text-danger">*</span>
      {{ __('common/address.address_1') }}</label>
    <input type="text" class="form-control" name="address_1" value="" required
           placeholder="{{ __('common/address.address_1') }}"/>
    <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' =>
      __('common/address.address_1')]) }}</span>
  </div>
  <div class="row gx-2">
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="Address_1">{{ __('common/address.address_2') }}</label>
        <input type="text" class="form-control" name="address_2" value=""
               placeholder="{{ __('common/address.address_2') }}"/>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="zipcode">
          <span class="text-danger">*</span>
          {{ __('common/address.zipcode') }}
        </label>
        <input type="text" class="form-control" name="zipcode" value="" required
               placeholder="{{ __('common/address.zipcode') }}"/>
        <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' =>
          __('common/address.zipcode')]) }}</span>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="city">
          <span class="text-danger">*</span>
          {{ __('common/address.city') }}
        </label>
        <input type="text" class="form-control" name="city" value="" required placeholder="City"/>
        <span class="invalid-feedback" role="alert">
          {{ __('front/common.error_required', ['name' => __('common/address.city')]) }}
        </span>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="country_code">
          <span class="text-danger">*</span>
          {{ __('common/address.country') }}
        </label>
        <select class="form-select" name="country_code" required></select>
        <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' =>
          __('common/address.country')]) }}</span>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="state">
          <span class="text-danger">*</span>
          {{ __('common/address.state') }}
        </label>
        <select class="form-select" name="state_code" required disabled></select>
        <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' =>
          __('common/address.state')]) }}</span>
      </div>
    </div>
    <div class="col-6">
      <div class="form-group mb-4">
        <label class="form-label" for="phone">
          <span class="text-danger">*</span>
          {{ __('common/address.phone') }}
        </label>
        <input type="text" class="form-control" name="phone" value="" required
               placeholder="{{ __('common/address.phone') }}"/>
        <span class="invalid-feedback" role="alert">{{ __('front/common.error_required', ['name' =>
          __('common/address.phone')]) }}</span>
      </div>
    </div>

    <div class="col-6">
      <div class="form-group mb-4 d-flex gap-3">
        <label class="form-label" for="default">{{__('front/common.default')}}</label>
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" role="switch" id="default" name="default" value="1">
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex justify-content-center">
    <button type="button" class="btn btn-primary btn-lg form-submit w-50">{{ __('front/common.submit') }}</button>
  </div>
</form>

@push('footer')
  <script>
    const settingCountryCode = @json(system_setting('country_code') ?? '');
    const settingStateCode = @json(system_setting('state_code') ?? '');

    inno.validateAndSubmitForm('.address-form', function (data) {
      if (typeof updateAddress === 'function') {
        updateAddress(data);
      }
    });

    getCountries();

    if (settingCountryCode) {
      $('select[name="country_code"]').val(settingCountryCode);
      getZones(settingCountryCode);
    }

    $(document).on('change', 'select[name="country_code"]', function () {
      var countryId = $(this).val();
      getZones(countryId);
    });

    // Get all country data
    function getCountries() {
      axios.get('{{ front_route('countries.index') }}').then(function (res) {
        var countries = res.data;
        var countrySelect = $('select[name="country_code"]');
        countrySelect.empty();
        countrySelect.append('<option value="">{{ __('front/common.please_choose') }}</option>');
        countries.forEach(function (country) {
          countrySelect.append('<option value="' + country.code + '"' + (country.code == settingCountryCode ? ' selected' : '') + '>' + country.name + '</option>');
        });
      });
    }

    function getZones(countryId, callback = null) {
      axios.get('{{ front_route('countries.index') }}/' + countryId).then(function (res) {
        var zones = res.data;
        var zoneSelect = $('select[name="state_code"]');
        zoneSelect.empty();

        if (zones.length === 0) {
          zoneSelect.prop('disabled', true);
          zoneSelect.prop('required', false);
          zoneSelect.append('<option value="">N/A</option>');
        } else {
          zoneSelect.prop('disabled', false);
          zoneSelect.prop('required', true);
          zoneSelect.append('<option value="">{{ __('front/common.please_choose') }}</option>');
          zones.forEach(function (zone) {
            zoneSelect.append('<option value="' + zone.code + '">' + zone.name + '</option>');
          });
        }

        if (typeof callback === 'function') {
          callback();
        }
      });
    }

    function clearForm() {
      const addressForm = $('.address-form');
      addressForm[0].reset();
      addressForm.removeClass('was-validated');

      addressForm.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    }
  </script>
@endpush
