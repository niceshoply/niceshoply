{{--
================================================================================
【文件说明】
  购物车页面。展示当前用户购物车中的所有商品，支持修改数量、选择/取消选择、
  删除单品、全选/取消全选，并显示已选商品的合计金额，最终引导用户进入结账流程。

【对应路由 / 控制器】
  路由名称：carts.index
  HTTP 方法：GET
  控制器：Front\CartController@index（或所在包的等价方法）

【可用变量】（由控制器注入到视图）
  $list          array    购物车商品列表，每项包含：
                            - id              int     购物车行 ID
                            - product_name    string  商品名称
                            - sku_code        string  SKU 编号
                            - variant_label   string  规格描述（如"红色 / L"）
                            - image           string  商品图片 URL
                            - url             string  商品详情页 URL
                            - price_format    string  单价（已格式化，含货币符号）
                            - quantity        int     数量
                            - subtotal_format string  小计（已格式化）
                            - selected        bool    是否已勾选
                            - is_stock_enough bool    库存是否充足
                            - item_type       string  商品类型（'normal' 为普通商品）
                            - item_type_label string  异常类型标签（下架/失效等）
                            - options         array   自定义选项列表，每项包含：
                                - option_id             int
                                - option_name           string
                                - option_value_name     string
                                - price_adjustment      float   价格调整值
                                - price_adjustment_format string 已格式化的调整值
  $total         string   已选商品件数（如 "3 items"）
  $amount_format string   已选商品合计金额（已格式化，含货币符号）

【Sections】
  body-class     → 页面 body 追加 CSS 类：page-cart
  content        → 页面主体内容
  header (push)  → 在 <head> 注入 Vue 3 CDN 脚本

【前端交互】
  框架：Vue 3（CDN，setup() 组合式 API）
  挂载点：#app-cart
  核心响应式数据：
    - list           ref    购物车商品数组（初始值来自 PHP $list）
    - total          ref    已选件数文本
    - amount_format  ref    合计金额文本
  计算属性：
    - allSelected      bool   是否全选（仅统计 item_type==='normal' 的商品）
    - selectedItems    array  已选中的普通商品列表
    - hasStockNotEnough bool  已选商品中是否存在库存不足的情况
  API 接口（依赖全局 urls 对象）：
    - urls.front_cart_add   PUT    /{id}     修改数量
    - urls.front_cart_add   DELETE /{id}     删除商品
    - urls.front_cart_add   POST   /select   批量勾选
    - urls.front_cart_add   POST   /unselect 批量取消勾选
    - urls.front_checkout           跳转结账页
  辅助：axios（全局封装，响应自动解包为 res），inno.msg() 显示提示

【插件钩子】
  @hookinsert('cart.top')           购物车容器顶部（适合插入横幅/公告）
  @hookinsert('cart.table.before')  商品表格之前（适合插入筛选/批量操作栏）
  @hookinsert('cart.table.after')   商品表格之后（适合插入优惠券输入框等）
  @hookinsert('cart.bottom')        购物车容器底部（适合插入推荐商品、广告等）

【自定义建议】
  1. 修改右侧汇总卡片样式：找 .cart-data 容器，可自由调整布局和配色。
  2. 如需在购物车添加优惠券功能，在 @hookinsert('cart.table.after') 处插入表单，
     并调用对应的优惠券 API 更新合计。
  3. item_type !== 'normal' 的商品（如赠品、Bundle）不可修改数量/删除，
     自定义时注意保留此逻辑判断。
  4. 空购物车状态由 v-if="list.length" / v-else 控制，可替换空状态图片和文案。
================================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-cart')

@section('content')
  @push('header')
    <script src="{{ asset('vendor/vue/3.5/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
  @endpush

  <x-front-breadcrumb type="route" value="carts.index" title="{{ __('front/cart.cart') }}" />

  @hookinsert('cart.top')

  <div class="container">
    @if (session()->has('errors'))
      <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4" />
    @endif
    @if (session('error'))
      <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-4" />
    @endif
    @if (session('success'))
      <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4" />
    @endif

    <div id="app-cart" v-cloak>
      <div class="row" v-if="list.length">
        <div class="col-12 col-md-9">
          @hookinsert('cart.table.before')

          <table class="table products-table align-middle">
            <thead>
              <tr>
                <th scope="col">
                  <input class="form-check-input product-all-check" type="checkbox"
                    :checked="allSelected"
                    @change="toggleAllSelection">
                </th>
                <th scope="col">{{ __('front/cart.product') }}</th>
                <th scope="col"></th>
                <th scope="col">{{ __('front/cart.price') }}</th>
                <th scope="col">{{ __('front/cart.quantity') }}</th>
                <th scope="col">{{ __('front/cart.subtotal') }}</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in list" :key="item.id" :data-id="item.id">
                <td class="td-product-check">
                  <input class="form-check-input product-item-check" :value="item.id" type="checkbox"
                    :checked="item.selected"
                    @change="updateSelection($event.target.checked, [item.id])"
                    :disabled="item.item_type !== 'normal'">
                </td>
                <td class="td-image">
                  <div class="product-image"><img :src="item.image" class="img-fluid"></div>
                </td>
                <td class="td-product-info">
                  <div class="product-item">
                    <div class="product-info">
                      <div class="product-name">
                        <a :href="item.url">@{{ item.product_name }}</a>
                        <div class="text-secondary mt-1">
                          @{{ item.sku_code }}
                          <template v-if="item.variant_label">
                            - @{{ item.variant_label }}
                          </template>
                          <!-- 显示选项值 -->
                          <template v-if="item.options && item.options.length">
                            <div class="product-options mt-2">
                              <div v-for="option in item.options" :key="option.option_id" class="option-item">
                                <span class="option-name">@{{ option.option_name }}:</span>
                                <span class="option-value">@{{ option.option_value_name }}</span>
                                <span v-if="option.price_adjustment != 0" class="price-adjustment text-muted">
                                  (@{{ option.price_adjustment_format }})
                                </span>
                              </div>
                            </div>
                          </template>
                          <span v-if="!item.is_stock_enough" class="badge bg-danger ms-2">
                            {{ __('front/common.stock_not_enough') }}
                          </span> 
                          <span v-if="item.item_type_label" class="badge bg-danger ms-2">
                            @{{ item.item_type_label }}
                          </span>
                        </div>
                      </div>
                      <div class="mb-price mt-1">@{{ item.price_format }}</div>
                      <div class="quantity-wrap mt-1 d-lg-none" v-if="item.item_type === 'normal'">
                        <div class="minus" @click="updateQuantity(item.id, item.quantity - 1)">
                          <i class="bi bi-dash-lg"></i>
                        </div>
                        <input type="number" class="form-control" v-model.number="item.quantity"
                          @change="updateQuantity(item.id, item.quantity)">
                        <div class="plus" @click="updateQuantity(item.id, item.quantity + 1)">
                          <i class="bi bi-plus-lg"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="td-price">@{{ item.price_format }}</td>
                <td class="td-quantity d-none d-lg-table-cell">
                  <div class="quantity-wrap" v-if="item.item_type === 'normal'">
                    <div class="minus" @click="updateQuantity(item.id, item.quantity - 1)">
                      <i class="bi bi-dash-lg"></i>
                    </div>
                    <input type="number" class="form-control" v-model.number="item.quantity"
                      @change="updateQuantity(item.id, item.quantity)">
                    <div class="plus" @click="updateQuantity(item.id, item.quantity + 1)">
                      <i class="bi bi-plus-lg"></i>
                    </div>
                  </div>
                  <div v-else>@{{ item.quantity }}</div>
                </td>
                <td class="td-subtotal">@{{ item.subtotal_format }}</td>
                <td class="td-delete">
                  <div class="delete-cart text-danger fs-5 cursor-pointer"
                    v-if="item.item_type === 'normal'"
                    @click="deleteItem(item.id)">
                    <i class="bi bi-x-circle-fill"></i>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          @hookinsert('cart.table.after')
        </div>

        <div class="col-12 col-md-3">
          <div class="cart-data">
            <div class="title">{{ __('front/cart.cart_total') }}</div>
            <ul class="cart-data-list">
              <li><span>{{ __('front/cart.selected') }} </span><span class="total-total">@{{ total }}</span></li>
              <li><span>{{ __('front/cart.total') }}</span><span class="total-amount">@{{ amount_format }}</span></li>
            </ul>
            @if(!system_setting('disable_online_order'))
              <button class="btn btn-primary btn-lg fw-bold w-100 to-checkout"
                :disabled="!selectedItems.length || hasStockNotEnough"
                @click="goToCheckout">
                {{ __('front/cart.go_checkout') }}
              </button>
            @endif
          </div>
        </div>
      </div>
      <div v-else class="text-center pm-5 pb-5">
        <img src="{{ asset('images/icons/empty-cart.svg') }}" class="img-fluid w-max-300 mb-5">
        <h2>{{ __('front/cart.empty_cart') }}</h2>
        <a class="btn btn-primary btn-lg mt-3"
          href="{{ front_route('home.index') }}">{{ __('front/cart.continue') }}</a>
      </div>
    </div>
  </div>

  @hookinsert('cart.bottom')
@endsection

@push('footer')
  <script>
    const { createApp, ref, computed } = Vue

    createApp({
      setup() {
        const list = ref(@json($list))
        const total = ref(@json($total))
        const amount_format = ref(@json($amount_format))

        // Computed properties
        const allSelected = computed(() => {
          const normalItems = list.value.filter(item => item.item_type === 'normal')
          return normalItems.length > 0 && normalItems.every(item => item.selected)
        })

        const selectedItems = computed(() => {
          return list.value.filter(item => item.selected && item.item_type === 'normal')
        })

        const hasStockNotEnough = computed(() => selectedItems.value.some(item => !item.is_stock_enough));

        // Methods
        const updateCartState = (data) => {
          list.value = data.list
          total.value = data.total_format
          amount_format.value = data.amount_format
          $('.header-cart-icon .icon-quantity').text(data.total_format)
        }

        const updateQuantity = async (id, quantity) => {
          const item = list.value.find(item => item.id === id)
          if (!item || item.item_type !== 'normal' || quantity < 1) return

          try {
            const res = await axios.put(`${urls.front_cart_add}/${id}`, { quantity })
            if (res.success) {
              inno.msg(res.message)
              updateCartState(res.data)
            }
          } catch (error) {
            console.error('Failed to update quantity:', error)
          }
        }

        const updateSelection = async (selected, ids) => {
          const normalIds = ids.filter(id => {
            const item = list.value.find(item => item.id === id)
            return item && item.item_type === 'normal'
          })

          if (!normalIds.length) return

          try {
            const res = await axios.post(`${urls.front_cart_add}/${selected ? 'select' : 'unselect'}`, {
              cart_ids: normalIds
            })
            if (res.success) {
              inno.msg(res.message)
              updateCartState(res.data)
            }
          } catch (error) {
            console.error('Failed to update selection:', error)
          }
        }

        const toggleAllSelection = () => {
          const normalIds = list.value
            .filter(item => item.item_type === 'normal')
            .map(item => item.id)
          updateSelection(!allSelected.value, normalIds)
        }

        const deleteItem = async (id) => {
          const item = list.value.find(item => item.id === id)
          if (!item || item.item_type !== 'normal') return

          try {
            const res = await axios.delete(`${urls.front_cart_add}/${id}`)
            if (res.success) {
              inno.msg(res.message)
              if (list.value.length === 1) {
                window.location.reload()
                return
              }
              updateCartState(res.data)
            }
          } catch (error) {
            console.error('Failed to delete item:', error)
          }
        }

        const goToCheckout = () => {
          if (!selectedItems.value.length) {
            inno.msg('Please select the product to checkout!')
            return
          }
          window.location.href = urls.front_checkout
        }

        // Return reactive state and methods
        return {
          list,
          total,
          amount_format,
          allSelected,
          selectedItems,
          updateQuantity,
          updateSelection,
          toggleAllSelection,
          deleteItem,
          goToCheckout
        }
      }
    }).mount('#app-cart')
  </script>
@endpush
