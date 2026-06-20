{{--
  ============================================================
  【文件说明】
    用户中心 — 收货地址管理页。
    展示当前会员所有收货地址，支持新增、编辑、删除操作。
    新增/编辑通过 Bootstrap Modal（#addressModal）弹窗完成，
    操作结果通过 AJAX 提交至后端。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）     ：account.addresses.index
    路由名称（POST）    ：account.addresses.index（新增地址）
    路由名称（PUT）     ：account.addresses.index/{id}（更新地址）
    路由名称（DELETE）  ：account.addresses.index/{id}（删除地址）
    URL 示例：/{locale}/account/addresses
    控制器：Front\Account\AddressController@index / @store / @update / @destroy

  【可用变量】
    $addresses — array（含多个地址对象），每条含以下字段：
                   id           地址唯一 ID（用于编辑/删除操作）
                   name         收件人姓名
                   phone        联系电话
                   zipcode      邮政编码
                   address_1    地址第一行
                   address_2    地址第二行（可为空）
                   city         城市
                   state        省/州
                   country_name 国家名称
                   country_code 国家代码（用于加载地区选择器）
                   default      是否为默认地址（1 = 是）

  【Sections】
    body-class → 'page-addresses'
    content    → 地址列表 + 新增/编辑弹窗
    footer     → 地址增删改 JS（@push）

  【插件钩子】
    @hookinsert('account.addresses.top')    — 容器顶部
    @hookinsert('account.addresses.bottom') — 容器底部

  【子视图引用】
    shared.address-form — 地址表单（包含国家/省市/邮编等字段），在弹窗 modal-body 中渲染

  【自定义建议】
    - 地址卡片样式由 CSS 类 .address-card 控制，可在主题样式中自定义
    - getZones(country_code, callback) 是全局函数，用于根据国家代码动态加载省/市下拉
    - 删除前有 layer.confirm 二次确认弹窗，可修改 JS 中的提示文案
    - sub_string($address['name']) 用于截断超长姓名，可在主题全局函数中调整截断长度
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-addresses')

@section('content')
  <x-front-breadcrumb type="route" value="account.addresses.index" title="{{ __('front/account.addresses') }}"/>

  @hookinsert('account.addresses.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box addresses-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('common/address.address') }}</span>
            <button type="button"
                    class="btn btn-primary add-address">{{ __('common/address.add_new_address') }}</button>
          </div>
          <div class="row">
            @foreach($addresses as $index => $address)
              <div class="col-12 col-md-6">
                <div class="address-card" data-id="{{ $address['id'] }}">
                  <div class="address-card-header">
                    <h5 class="address-card-title">{{ sub_string($address['name']) }}</h5>
                    <div class="address-card-actions">
                      @if($address['default'])
                        <div class="bg-success text-white p-1 required rounded">
                          {{__('front/common.default')}}
                        </div>
                      @endif
                      <button type="button" class="btn btn-link edit-address">{{ __('front/common.edit') }}</button>
                      <button type="button" class="btn btn-link delete-address">{{ __('front/common.delete') }}</button>
                    </div>
                  </div>
                  <div class="address-card-body">
                    <p>{{ __('common/address.name') }}: {{ $address['name'] }}</p>
                    <p>{{ __('common/address.phone') }}: {{ $address['phone'] }}</p>
                    <p>{{ __('common/address.zipcode') }}: {{ $address['zipcode'] }}</p>
                    <p>{{ __('common/address.address_1') }}: {{ $address['address_1'] }}</p>
                    @if($address['address_2'])
                      <p>{{ __('common/address.address_2') }}: {{ $address['address_2'] }}</p>
                    @endif
                    <p>{{ __('common/address.region') }}: {{ $address['city'] }}, {{ $address['state'] }}
                      , {{ $address['country_name'] }}</p>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addressModalLabel">{{ __('common/address.address') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          @include('shared.address-form')
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.addresses.bottom')

@endsection

@push('footer')
  <script>
    const addresses = @json($addresses);
    const isDefault = $('#default');

    $('.add-address').on('click', function () {
      $('.address-form').find('input, select').each(function () {
        $(this).val('')
      })
      isDefault.val(1);

      $('#addressModal').modal('show');
    });

    $('.edit-address').on('click', function () {
      const id = $(this).closest('.address-card').data('id');
      const address = addresses.find(address => address.id === id);

      getZones(address.country_code, function () {
        $('.address-form').find('input, select').each(function () {
          $(this).val(address[$(this).attr('name')])
        })
        isDefault.val(1);
        if (address.default === 1) {
          isDefault.attr('checked', 'checked');
        } else {
          isDefault.removeAttr('checked');
        }
      })

      $('#addressModal').modal('show');
    });

    $('.delete-address').on('click', function () {
      const id = $(this).closest('.address-card').data('id');

      layer.confirm('{{ __('front/common.delete_confirm') }}', {
        btn: ['{{ __('front/common.confirm') }}', '{{ __('front/common.cancel') }}']
      }, function () {
        axios.delete(`{{ account_route('addresses.index') }}/${id}`).then(function (res) {
          if (res.success) {
            layer.msg(res.message, {icon: 1, time: 1000}, function () {
              window.location.reload()
            });
          }
        })
      });
    });

    function updateAddress(params) {
      const id = new URLSearchParams(params).get('id');
      const href = @json(account_route('addresses.index'));
      const method = id ? 'put' : 'post'
      const url = id ? `${href}/${id}` : href

      axios[method](url, params).then(function (res) {
        if (res.success) {
          $('#addressModal').modal('hide');
          inno.msg(res.message);
          window.location.reload();
        }
      })
    }
  </script>
@endpush
