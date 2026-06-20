{{--
================================================================================
【文件说明】
  结账页面（填写收货地址、选择配送方式、选择支付方式、使用余额、提交订单）。
  用户在此完成下单前的所有信息确认，点击"下单"后跳转到订单支付页。

【对应路由 / 控制器】
  路由名称：checkout.index
  HTTP 方法：GET（显示页面）/ PUT（更新结账状态，AJAX）/ POST（确认提交，AJAX）
  控制器 GET：Front\CheckoutController@index
  控制器 PUT：Front\CheckoutController@update（实时更新运费/合计）
  控制器 POST：Front\CheckoutController@confirm（提交订单，返回订单编号）

【可用变量】（由控制器注入到视图）
  $address_list        array   用户已保存的收货地址列表，每项包含：
                                 id, name, phone, zipcode,
                                 address_1, address_2, city, state,
                                 country_code, country_name
  $shipping_methods    array   可用配送方式列表（按运营商分组），结构：
                                 [ { code, quotes: [ {code, name, cost_format, icon} ] } ]
  $billing_methods     array   可用支付方式列表：
                                 [ { code, name, icon } ]
  $fee_list            array   费用明细列表（运费、税、折扣等）：
                                 [ { title, total_format } ]
  $amount              float   订单合计金额（原始数值）
  $cart_list           array   结账商品列表（来自购物车已选商品），每项包含：
                                 product_name, sku_code, variant_label,
                                 item_type_label, quantity, image,
                                 price_format, options[]
  $checkout            array   当前结账会话状态：
                                 shipping_address_id, billing_address_id,
                                 shipping_method_code, billing_method_code,
                                 reference.balance（已使用余额）
  $balance_amount      float   用户账户余额（可用于抵扣，未登录时为 0）
  $balance_amount_format string 余额格式化文本
  $allocation          array|null  仓库分配信息（含 messages 数组，用于展示库存来源）

【Sections】
  body-class   → page-checkout
  content      → 页面主体
  header(push) → 注入 Vue 3 CDN

【前端交互】
  框架：Vue 3（setup() 组合式 API）
  挂载点：#app-checkout
  响应式数据：
    source（reactive）：
      - addresses          地址列表
      - shippingMethods    配送方式列表
      - billingMethods     支付方式列表
      - addressEdit        bool  是否显示地址表单（true=新增/编辑模式）
      - same_as_shipping_address  bool  账单地址同收货地址
      - feeList            费用明细
      - totalAmount        float  合计金额（数值）
      - totalAmountFormat  string 合计金额（文本）
      - balanceAmount      float  可用余额
      - balanceAmountFormat string 余额格式化文本
      - allocation         仓库分配信息
    current（reactive）：
      - shipping_address_id   int    选中的收货地址 ID
      - billing_address_id    int    选中的账单地址 ID
      - shipping_method_code  string 选中的配送方式代码
      - billing_method_code   string 选中的支付方式代码
      - comment               string 订单备注
      - balance               float  本次抵扣余额
      - reference.balance     float  已保存的抵扣余额
  关键方法：
    - updateCheckout(key, value)   更新 current 并 PUT /checkout 刷新运费/合计
    - updateShippingAddress(id)    切换收货地址并自动选首个配送方式
    - updateAddress(params)        新增/编辑地址（POST/PUT /addresses）
    - addressEdit(bool)            切换地址表单显示状态
    - submitCheckout()             POST /checkout/confirm 提交订单 → 跳转支付页
    - submitBalance()              PUT /checkout 提交余额抵扣
    - validateInput(e)             输入框过滤非数字字符
  依赖全局对象：
    - urls.front_cart_add  （地址相关）
    - api.address / api.checkout / api.checkoutConfirm
    - layer（layer.js，加载遮罩）
    - inno.msg() / inno.openLogin() / inno.getBase()

【插件钩子】
  @hookinsert('checkout.top')               结账页顶部
  @hookinsert('checkout.products.before')   商品列表前（适合插入促销信息）
  @hookinsert('checkout.products.after')    商品列表后（适合插入优惠券输入框）
  @hookinsert('checkout.confirm.before')    下单按钮前（适合插入条款勾选框）
  @hookinsert('checkout.bottom')            结账页底部

【自定义建议】
  1. 地址表单来自 @include('shared.address-form')，可在该共享模板中修改字段。
  2. 新增自定义费用行：在 $fee_list 数据源中添加，前端 source.feeList 会自动渲染。
  3. 余额抵扣区块受 current_customer() 判断控制，访客不可见；如需开放可移除该条件。
  4. 仓库分配提示（allocation.messages）可在商品列表下方展示库存来源，
     local=绿色、fallback=黄色、insufficient=红色，可自定义 CSS 颜色。
  5. submitCheckout 成功后跳转规则：/orders/{number}/pay，可修改为自定义支付落地页。
================================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-checkout')

@section('content')

  @push('header')
    <script src="{{ asset('vendor/vue/3.5/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
  @endpush

  <x-front-breadcrumb type="route" value="checkout.index" title="{{ __('front/checkout.checkout') }}" />

  @hookinsert('checkout.top')

  <div class="container checkout-container h-min-600">
    <div class="row" id="app-checkout" v-cloak>
      <div class="col-12 col-md-7">
        <div class="checkout-info">

          <div class="address-box">
            <div class="checkout-item" v-if="!source.addressEdit">
              <div class="addresses-wrap">
                <div class="shipping-address">
                  <div class="title-wrap">
                    <div class="title">
                      {{ __('front/checkout.shipping_address') }}
                    </div>
                    <div>
                      <span class="cursor-pointer" v-if="!source.addressEdit" @click="addressEdit(true)"><i
                          class="bi bi-plus-lg"></i>{{ __('front/checkout.create_address') }}</span>
                    </div>
                  </div>
                  <div class="checkout-select-wrap address-select" v-if="source.addresses.length && !source.addressEdit">
                    <div :class="['select-item', current.shipping_address_id == address.id ? 'active' : '']"
                      v-for="address, index in source.addresses" :key="address.id"
                      @click="updateShippingAddress(address.id)">
                      <div class="left">
                        <i class="bi bi-circle"></i>
                        <div class="select-title">
                          <div class="address-name mb-1">@{{ address.name }} @{{ address.phone }}
                            @{{ address.zipcode }}
                          </div>
                          <div class="address-info">@{{ address.address_1 }} @{{ address.address_2 }} @{{ address.city }}
                            @{{ address.state }} @{{ address.country_name }}
                          </div>
                        </div>
                      </div>
                      <div class="edit-address text-decoration-underline text-secondary" @click.stop="editAddress(index)">
                        {{ __('front/common.edit') }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="checkout-item" v-if="!source.addressEdit">
              <div class="addresses-wrap">
                <div class="shipping-address">
                  <div class="title-wrap">
                    <div class="title">{{ __('front/checkout.billing_address') }}</div>
                    <div>
                      <label class="form-check-label" v-if="!source.addressEdit">
                        <input class="form-check-input" type="checkbox" v-model="source.same_as_shipping_address">
                        {{ __('front/checkout.same_shipping_address') }}
                      </label>
                    </div>
                  </div>
                  <div v-if="!source.same_as_shipping_address">
                    <div class="checkout-select-wrap address-select" v-if="source.addresses.length && !source.addressEdit">
                      <div :class="['select-item', current.billing_address_id == address.id ? 'active' : '']"
                        v-for="address, index in source.addresses" :key="address.id"
                        @click="updateCheckout('billing_address_id', address.id)">
                        <div class="left">
                          <i class="bi bi-circle"></i>
                          <div class="select-title">
                            <div class="address-name mb-1">@{{ address.name }} @{{ address.phone }}
                              @{{ address.zipcode }}
                            </div>
                            <div class="address-info">@{{ address.address_1 }} @{{ address.address_2 }}
                              @{{ address.state }} @{{ address.city }} @{{ address.country_id }}
                            </div>
                          </div>
                        </div>
                        <div class="edit-address text-decoration-underline text-secondary" @click="editAddress(index)">
                          {{ __('front/common.edit') }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-show="source.addressEdit">
              <div class="checkout-item">
                <div class="title-wrap">
                  <div class="title">{{ __('front/checkout.create_address') }}</div>
                  @if (!current_customer())
                    <span class="cursor-pointer btn btn-sm btn-outline-primary" @click="login"><i
                        class="bi bi-box-arrow-in-right"></i> {{ __('front/common.login') }}</span>
                  @endif
                  <span class="cursor-pointer" v-if="source.addresses.length" @click="addressEdit(false)"><i
                      class="bi bi-plus-lg"></i> {{ __('front/checkout.cancel_create') }}</span>
                </div>
                @include('shared.address-form')
              </div>
            </div>
          </div>

          <div class="checkout-item">
            <div class="title-wrap">
              <div class="title">{{ __('front/checkout.shipping_methods') }}</div>
            </div>
            <div class="checkout-select-wrap">
              <div v-if="!current.shipping_address_id" class="alert alert-warning">
                <i class="bi bi-exclamation-circle-fill"></i> {{ __('front/checkout.please_create_address') }}
              </div>
              <div v-else>
                <div v-for="item in source.shippingMethods" :key="item.code">
                  <div v-for="quote in item.quotes" :key="quote.code"
                    @click="updateCheckout('shipping_method_code', quote.code)"
                    :class="['select-item', current.shipping_method_code == quote.code ? 'active' : '']">
                    <div class="left">
                      <i class="bi bi-circle"></i>
                      <div class="select-title">
                        <span class="name"> @{{ quote.name }}</span> &nbsp;&nbsp;
                        <span class="cost"> @{{ quote.cost_format }}</span>
                      </div>
                    </div>
                    <div class="icon"><img :src="quote.icon" class="img-fluid"></div>
                  </div>
                </div>
                <div v-if="!source.shippingMethods.length" class="alert alert-warning">
                  <i class="bi bi-exclamation-circle-fill"></i> {{ __('front/checkout.no_shipping_methods') }}
                </div>
              </div>
            </div>
          </div>

          <div class="checkout-item">
            <div class="title-wrap">
              <div class="title">{{ __('front/checkout.billing_methods') }}</div>
            </div>
            <div class="checkout-select-wrap">
              <div :class="['select-item', current.billing_method_code == item.code ? 'active' : '']"
                v-for="item in source.billingMethods" :key="item.code"
                @click="updateCheckout('billing_method_code', item.code)">
                <div class="left">
                  <i class="bi bi-circle"></i>
                  <div class="select-title">@{{ item.name }}</div>
                </div>
                <div class="icon"><img :src="item.icon" class="img-fluid"></div>
              </div>
              <div v-if="!source.billingMethods.length" class="alert alert-warning"><i
                  class="bi bi-exclamation-circle-fill"></i> {{ __('front/checkout.no_billing_methods') }}</div>
            </div>
          </div>

          <div class="checkout-item">
            <div class="title-wrap">
              <div class="title">{{ __('front/checkout.order_comment') }}</div>
            </div>
            <div class="checkout-select">
              <textarea class="form-control" rows="4" v-model="current.comment"
                placeholder="{{ __('front/checkout.order_comment') }}"></textarea>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-5">
        <div class="checkout-data">
          <div class="checkout-data-content">
            <div class="title-wrap">
              <div class="title">{{ __('front/checkout.my_order') }}</div>
            </div>
            <div class="products-table">

              @hookinsert('checkout.products.before')
              
              @if (!empty($cart_list))
                <div class="products-table-title">
                  <span>{{ __('front/cart.product') }}</span>
                  <span class="text-end">{{ __('front/cart.price') }}</span>
                </div>
                <div class="products-table-wrap">
                  @foreach ($cart_list as $product)
                    <div class="products-table-list">
                      <div>
                        <div class="product-item">
                          <div class="product-image"><img src="{{ $product['image'] }}" class="img-fluid"></div>
                          <div class="product-info">
                            <div class="name">{{ $product['product_name'] }}</div>
                            <div class="sku mt-2 text-secondary">{{ $product['sku_code'] }}
                              @if ($product['variant_label'])
                                - {{ $product['variant_label'] }}
                              @endif
                              @if ($product['item_type_label'])
                                <span class="badge bg-danger ms-2">{{ $product['item_type_label'] }}</span>
                              @endif
                              x {{ $product['quantity'] }}
                            </div>
                            @if (!empty($product['options']))
                              <div class="product-options mt-2">
                                @foreach ($product['options'] as $option)
                                  <div class="option-item text-muted small">
                                    <strong>{{ $option['option_name'] }}:</strong> {{ $option['option_value_name'] }}
                                    @if ($option['price_adjustment'] != 0)
                                      <span class="text-primary">({{ $option['price_adjustment'] > 0 ? '+' : '' }}{{ currency_format($option['price_adjustment']) }})</span>
                                    @endif
                                  </div>
                                @endforeach
                              </div>
                            @endif
                          </div>
                          <div class="warehouse-info small mt-1" v-if="source.allocation && source.allocation.messages">
                            <template v-for="msg in source.allocation.messages" :key="msg.sku_code + msg.type">
                              <span v-if="msg.sku_code === '{{ $product['sku_code'] }}'"
                                :class="{'text-success': msg.type === 'local', 'text-warning': msg.type === 'fallback', 'text-danger': msg.type === 'insufficient'}">
                                <i :class="{'bi bi-check-circle': msg.type === 'local', 'bi bi-exclamation-triangle': msg.type === 'fallback', 'bi bi-x-circle': msg.type === 'insufficient'}"></i>
                                @{{ msg.message }}
                              </span>
                            </template>
                          </div>
                        </div>
                      </div>
                      <div class="text-end">{{ $product['price_format'] }}</div>
                    </div>
                  @endforeach
                </div>
              @endif
              
              @hookinsert('checkout.products.after')

            </div>

            @if (current_customer())
              <div class="border-top pt-3 pb-2">
                <div class="row">
                  <div class="col-12 d-flex align-items-center gap-3">
                    <div class="input-group flex-nowrap">
                      <span class="input-group-text">{{ default_currency()->symbol_left }}</span>
                      <input type="text" v-model="current.balance" class="form-control py-2"
                        placeholder="{{ __('front/transaction.balance_placeholder') }}"
                        aria-label="{{ __('front/transaction.balance') }}" @input="validateInput">
                    </div>
                    <button
                      :class="{
                          'disabled': parseFloat(current.balance) > source.balanceAmount || parseFloat(current
                              .balance) >= source.totalAmount || isNaN(parseFloat(current.balance))
                      }"
                      class="input-group-text btn btn-primary py-2" id="addon-wrapping" @click="submitBalance"
                      :disabled="parseFloat(current.balance) > source.balanceAmount || parseFloat(current.balance) >= source.totalAmount ||
                          isNaN(parseFloat(current.balance))"
                      style="cursor: pointer;">
                      {{ __('front/transaction.confirm') }}
                    </button>
                  </div>
                </div>
                <div class="pt-1 fs-7 d-flex gap-3" style="font-size: 10px;">
                  <span>{{ __('front/transaction.available_balance') }}: @{{ source.balanceAmountFormat }}</span>
                  <span class="fs-7 text-danger" style="font-size: 10px;"
                    v-if="parseFloat(current.balance) > source.balanceAmount">{{ __('front/transaction.input_should_balance') }}</span>
                  <span class="fs-7 text-danger" style="font-size: 10px;"
                    v-else-if="parseFloat(current.balance) >= source.totalAmount">{{ __('front/transaction.input_balance_total') }}</span>
                </div>
              </div>
            @endif

            <ul class="cart-data-list">
              <li class="cart-data-list" v-for="fee in source.feeList" :key="fee.title">
                <span>@{{ fee.title }}</span><span> @{{ fee.total_format }} </span>
              </li>
              <li><span>{{ __('front/cart.total') }}</span><span>@{{ source.totalAmountFormat }}</span></li>
            </ul>

            @hookinsert('checkout.confirm.before')
            <button class="btn btn-primary btn-lg fw-bold w-100 to-checkout" :disabled="isCheckout" type="button"
              @click="submitCheckout">{{ __('front/checkout.place_order') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  @hookinsert('checkout.bottom')

@endsection

@push('footer')
  <script>
    const {
      createApp,
      ref,
      reactive,
      onMounted,
      computed
    } = Vue
    const api = {
      address: @json(front_route('addresses.store')),
      checkout: @json(front_route('checkout.index')),
      checkoutConfirm: @json(front_route('checkout.confirm')),
    }

    const checkoutApp = createApp({
      setup() {
        const source = reactive({
          addresses: @json($address_list),
          shippingMethods: @json($shipping_methods),
          billingMethods: @json($billing_methods),
          addressEdit: @json($address_list).length ? false : true,
          same_as_shipping_address: true,
          feeList: @json($fee_list),
          totalAmount: @json($amount),
          totalAmountFormat: @json(currency_format($amount)),
          balanceAmount: @json($balance_amount ?? 0),
          balanceAmountFormat: @json($balance_amount_format ?? '0'),
          allocation: @json($allocation ?? null),
        })

        const current = reactive({
          shipping_address_id: @json($checkout['shipping_address_id'] ?? 0),
          billing_address_id: @json($checkout['billing_address_id'] ?? 0),
          shipping_method_code: @json($checkout['shipping_method_code'] ?? ''),
          billing_method_code: @json($checkout['billing_method_code'] ?? ''),
          comment: '',
          balance: 0,
          reference: {
            balance: Number(@json($checkout['reference']['balance'] ?? 0))
          },
        })

        current.balance = current.reference.balance;

        const isCheckout = computed(() => {
          return !current.shipping_address_id || !current.billing_address_id || !current.shipping_method_code || !
            current.billing_method_code
        })

        editAddress = (index) => {
          source.addressEdit = true
          const address = source.addresses[index]

          getZones(address.country_code, function() {
            $('.address-form').find('input, select').each(function() {
              $(this).val(address[$(this).attr('name')])
            })
          })
        }

        const updateCheckout = (key, value) => {
          current[key] = value;
          if (source.same_as_shipping_address && key === 'shipping_address_id') {
            current.billing_address_id = value;
          }

          axios.put(api.checkout, current).then(function(res) {
            if (res.success) {
              source.feeList = res.data.fee_list;
              source.totalAmount = res.data.amount;
              source.totalAmountFormat = res.data.amount_format;
              source.shippingMethods = res.data.shipping_methods;
              if (res.data.allocation) source.allocation = res.data.allocation;
            }
          });
        }

        const selectFirstShippingMethod = () => {
          if (source.shippingMethods.length && source.shippingMethods[0].quotes.length) {
            const firstQuote = source.shippingMethods[0].quotes[0];
            current.shipping_method_code = firstQuote.code;
            updateCheckout('shipping_method_code', firstQuote.code);
          }
        }

        const updateShippingAddress = (addressId) => {
          current.shipping_method_code = '';
          updateCheckout('shipping_address_id', addressId);

          axios.put(api.checkout, current).then(function(res) {
            if (res.success) {
              source.shippingMethods = res.data.shipping_methods;
              if (res.data.allocation) source.allocation = res.data.allocation;
              selectFirstShippingMethod();
            }
          });
        }

        const updateAddress = (params) => {
          const id = parseInt(new URLSearchParams(params).get('id'));
          const url = id ? api.address + '/' + id : api.address;
          const method = id ? 'put' : 'post';

          axios[method](url, params).then(function(res) {
            if (res.success) {
              inno.msg(res.message);
              
              if (id) {
                const index = source.addresses.findIndex(address => address.id === id);
                source.addresses[index] = res.data;
                updateShippingAddress(id);
              } else {
                source.addresses.push(res.data);
                if (source.addresses.length === 1) {
                  updateShippingAddress(res.data.id);
                }
              }

              source.addressEdit = false;
              clearForm();
            }
          });
        }

        const addressEdit = (status) => {
          source.addressEdit = status
          clearForm()
        }

        const submitCheckout = () => {
          layer.load(2, {
            shade: [0.3, '#fff']
          })
          axios.post(api.checkoutConfirm, current).then(function(res) {
            if (res.success) {
              layer.msg(res.message, {
                time: 1000
              }, function() {
                location.href = inno.getBase() + '/orders/' + res.data.number + '/pay'
              })
            }
          }).finally(function() {
            layer.closeAll('loading')
          });
        }

        const login = () => {
          inno.openLogin()
        }

        const submitBalance = () => {
          if (parseFloat(current.balance) <= source.balanceAmount && parseFloat(current.balance) < source.totalAmount) {
            axios.put(api.checkout, {
              reference: {
                balance: parseFloat(current.balance)
              }
            }).then(function(res) {
              if (res.success) {
                source.feeList = res.data.fee_list;
                source.totalAmount = res.data.amount;
                source.totalAmountFormat = res.data.amount_format;
              }
            }).catch(function(error) {
              console.error('Error:', error);
            });
          }
        }

        const validateInput = (event) => {
          let value = event.target.value;
          value = value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');
          if (value.startsWith('.')) {
            value = value.substring(1);
          }
          if (value !== event.target.value) {
            event.target.value = value;
          }
        }

        return {
          source,
          login,
          current,
          editAddress,
          updateCheckout,
          addressEdit,
          isCheckout,
          updateAddress,
          updateShippingAddress,
          submitCheckout,
          submitBalance,
          validateInput,
        }
      }
    }).mount('#app-checkout')

    function updateAddress(params) {
      checkoutApp.updateAddress(params)
    }
  </script>
@endpush
