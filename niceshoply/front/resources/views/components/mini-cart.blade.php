{{--
================================================================================
【文件说明】
  迷你购物车侧滑抽屉组件（Mini Cart）。
  使用 Bootstrap 5 Offcanvas 从右侧滑出，展示当前购物车的商品列表、
  数量调整、勾选/全选、删除、价格小计，以及结算/查看购物车按钮。
  购物车数据通过 Vue 3 + Axios 实现异步加载与实时更新，无需刷新页面。

  在布局文件中通过 Blade 组件标签调用（通常放在 <body> 尾部）：
    <x-front-mini-cart />
  触发打开抽屉的按钮示例（在 header 中）：
    <a data-bs-toggle="offcanvas" data-bs-target="#miniCart">购物车</a>

【注册方式】
  FrontServiceProvider 中以别名 "front-mini-cart" 注册：
    Blade::component('front-mini-cart', Components\MiniCart::class);

【可用变量 / Props】
  本组件为纯前端驱动，Blade 层面无需服务端注入变量。
  所有数据通过以下全局 JS 变量获取（由布局文件在 <script> 中注入）：
  - urls.front_cart_mini   — 获取购物车列表的 API 接口 URL（GET）
  - urls.front_cart_add    — 购物车操作基础 URL（PUT 更新数量、DELETE 删除）
  - urls.front_checkout    — 结算页面 URL
  - urls.front_cart        — 购物车页面 URL
  - urls.front_base        — 网站首页 URL
  - asset_url              — 静态资源根路径（用于拼接图标图片路径）

  Vue 3 响应式状态说明：
  - cartItems              — 购物车商品列表数组，每项包含：
      id、product_name、image、url、sku_code、variant_label
      options[]（含 option_name、option_value_name、price_adjustment_format）
      quantity、price_format、subtotal_format、selected、item_type、item_type_label
  - totalAmount            — 已选商品的格式化总金额字符串
  - isEmpty                — 计算属性，购物车是否为空
  - allSelected            — 计算属性，普通商品是否全部已选中

  item_type 说明：
  - 'normal'   — 普通商品，支持数量修改、勾选
  - 其他类型    — 赠品/捆绑等特殊商品，数量和勾选均为只读

【插件钩子】
  本组件内部暂无 @hookinsert 点位。
  若需扩展购物车侧滑面板的内容，可通过插件替换整个组件类实现。

【自定义建议】
  开发新主题时，可以：
  1. 修改 offcanvas 宽度（默认 400px）以适配不同设计需求。
  2. 空购物车状态的图片路径为 asset_url + 'images/icons/empty-cart.svg'，
     可替换为主题自定义图片。
  3. Vue 实例挂载在 #miniCart 元素上（window.cartApp），
     若需在其他组件中触发购物车刷新，可调用 window.cartApp.loadCart()。
  4. 购物车图标数量徽标通过 .header-cart-icon .icon-quantity 元素更新，
     请确保 header 中购物车图标包含该 class 结构。
  5. 本组件通过 @push('footer') 将 Vue 脚本注入到布局的 @stack('footer') 位置，
     请确保布局文件中存在 @stack('footer')。
  6. Vue 3 CDN 脚本路径：vendor/vue/3.5/vue.global[.prod].js，
     生产环境自动加载 .prod 版本（通过 config('app.debug') 判断）。
================================================================================
--}}
<!-- Mini Cart Component -->
<div class="offcanvas offcanvas-end" style="width: 400px;" tabindex="-1" id="miniCart" aria-labelledby="miniCartLabel">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="miniCartLabel">{{ __('front/cart.cart') }}</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0 d-flex flex-column" style="height: calc(100vh - 60px);" v-cloak>
    <!-- Empty cart state -->
    <div v-if="isEmpty" class="text-center py-5">
      <img :src="asset_url + 'images/icons/empty-cart.svg'" class="img-fluid" style="max-width: 200px;" class="mb-4">
      <h5>{{ __('front/cart.empty_cart') }}</h5>
      <a class="btn btn-primary mt-3" :href="urls.front_base">{{ __('front/cart.continue') }}</a>
    </div>

    <!-- Cart content -->
    <div v-else class="d-flex flex-column h-100">
      <div class="cart-items flex-grow-1 overflow-auto p-3">
        <div v-for="item in cartItems" :key="item.id" class="py-3 border-bottom" :data-id="item.id">
          <div class="d-flex">
            <div class="d-flex align-items-center me-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" :id="'item-' + item.id" 
                  :checked="item.selected"
                  @change="updateSelection($event.target.checked, [item.id])"
                  :disabled="item.item_type !== 'normal'">
              </div>
            </div>
            <div class="flex-shrink-0" style="width: 80px; height: 80px;">
              <img :src="item.image" class="img-fluid w-100 h-100 object-fit-cover">
            </div>
            <div class="flex-grow-1 ms-3 overflow-hidden">
              <div class="text-truncate">
                <a :href="item.url" class="text-decoration-none text-body hover-primary">@{{ item.product_name }}</a>
              </div>
              <div class="text-secondary mt-1">
                @{{ item.sku_code }}
                <span v-if="item.variant_label">- @{{ item.variant_label }}</span>
                <!-- 显示选项值 -->
                <template v-if="item.options && item.options.length">
                  <div class="product-options mt-1">
                    <div v-for="option in item.options" :key="option.option_id" class="option-item small">
                      <span class="option-name">@{{ option.option_name }}:</span>
                      <span class="option-value">@{{ option.option_value_name }}</span>
                      <span v-if="option.price_adjustment != 0" class="price-adjustment text-muted">
                        (@{{ option.price_adjustment_format }})
                      </span>
                    </div>
                  </div>
                </template>
                <span v-if="item.item_type !== 'normal'" class="badge bg-danger ms-2">@{{ item.item_type_label }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="d-flex align-items-center gap-2">
                  <div class="fs-6">@{{ item.price_format }}</div>
                  <div class="d-flex align-items-center" style="width: 60px;">
                    <input type="number" 
                           :class="item.item_type !== 'normal' ? 'form-control form-control-sm text-center p-0 bg-light' : 'form-control form-control-sm text-center p-0'"
                           style="width: 60px; height: 26px;" 
                           v-model.number="item.quantity" 
                           min="1" 
                           @change="quantityChanged(item)"
                           :readonly="item.item_type !== 'normal'">
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <div class="text-primary small">@{{ item.subtotal_format }}</div>
                  <button type="button" class="btn btn-link text-danger p-0 border-0 d-flex align-items-center justify-content-center" 
                          style="width: 26px; height: 26px;" 
                          @click="deleteItem(item.id)">
                    <i class="bi bi-x-circle-fill"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cart footer -->
      <div class="border-top p-3">
        <div class="d-flex align-items-center mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="selectAll" 
              :checked="allSelected"
              @change="toggleSelectAll">
            <label class="form-check-label" for="selectAll">{{ __('front/cart.select_all') }}</label>
          </div>
          <div class="ms-auto">
            <span class="fs-5">{{ __('front/cart.total') }}</span>
            <span class="fs-5 text-primary ms-2">@{{ totalAmount }}</span>
          </div>
        </div>
        <a class="btn btn-primary btn-lg fw-bold w-100 to-checkout" :href="urls.front_checkout">
          {{ __('front/cart.go_checkout') }}
        </a>
        <a class="btn btn-outline-secondary btn-lg fw-bold w-100 mt-2" :href="urls.front_cart">
          {{ __('front/cart.view_cart') }}
        </a>
      </div>
    </div>
  </div>
</div>

@push('footer')
<script src="{{ asset('vendor/vue/3.5/vue.global' . (!config('app.debug') ? '.prod' : '') . '.js') }}"></script>
<script>
if (!window.cartApp) {
  const { createApp, ref, computed, onMounted } = Vue

  window.cartApp = createApp({
    setup() {
      // Reactive states
      const cartItems = ref([])
      const totalAmount = ref('')
      
      // Computed properties
      const isEmpty = computed(() => !cartItems.value.length)
      const allSelected = computed(() => {
        const normalItems = cartItems.value.filter(item => item.item_type === 'normal')
        return normalItems.length > 0 && normalItems.every(item => item.selected)
      })
      
      // Methods
      const loadCart = async () => {
        try {
          const response = await axios.get(urls.front_cart_mini)
          if (response.success) {
            cartItems.value = response.data.list || []
            totalAmount.value = response.data.amount_format
            updateCartIconQuantity(response.data.total_format)
          }
        } catch (error) {
          inno.msg('Failed to load cart', 'error')
        }
      }

      const updateSelection = async (selected, ids) => {
        // Filter normal product IDs
        const normalIds = ids.filter(id => {
          const item = cartItems.value.find(item => item.id === id)
          return item && item.item_type === 'normal'
        })

        if (!normalIds.length) return

        try {
          const response = await axios.post(`${urls.front_cart_add}/${selected ? 'select' : 'unselect'}`, {
            cart_ids: normalIds
          })
          if (response.success) {
            inno.msg(response.message)
            await loadCart()
          }
        } catch (error) {
          inno.msg('Failed to update selection', 'error')
        }
      }

      const toggleSelectAll = () => {
        const normalIds = cartItems.value
          .filter(item => item.item_type === 'normal')
          .map(item => item.id)
        updateSelection(!allSelected.value, normalIds)
      }

      const updateQuantity = async (id, quantity) => {
        if (quantity < 1) return
        
        try {
          const response = await axios.put(`${urls.front_cart_add}/${id}`, { quantity })
          if (response.success) {
            inno.msg(response.message)
            await loadCart()
            if (window.location.pathname.includes('/cart')) {
              window.location.reload()
            }
          }
        } catch (error) {
          inno.msg('Failed to update quantity', 'error')
        }
      }

      const deleteItem = async (id) => {
        try {
          const response = await axios.delete(`${urls.front_cart_add}/${id}`)
          if (response.success) {
            inno.msg(response.message)
            await loadCart()
            if (window.location.pathname.includes('/cart')) {
              window.location.reload()
            }
          }
        } catch (error) {
          inno.msg('Failed to delete item', 'error')
        }
      }

      const updateCartIconQuantity = (quantity) => {
        document.querySelectorAll('.header-cart-icon .icon-quantity').forEach(el => {
          el.textContent = quantity
        })
      }

      // Helper methods
      const increaseQuantity = (item) => {
        updateQuantity(item.id, item.quantity + 1)
      }

      const decreaseQuantity = (item) => {
        if (item.quantity > 1) {
          updateQuantity(item.id, item.quantity - 1)
        }
      }

      const quantityChanged = (item) => {
        if (item.quantity < 1) {
          item.quantity = 1
        }
        updateQuantity(item.id, item.quantity)
      }

      // Lifecycle hooks
      onMounted(() => {
        loadCart()
        
        // Listen for cart show event
        const miniCart = document.getElementById('miniCart')
        if (miniCart) {
          miniCart.addEventListener('show.bs.offcanvas', () => {
            loadCart()
          })
        }
      })

      return {
        cartItems,
        totalAmount,
        isEmpty,
        allSelected,
        urls,
        asset_url,
        increaseQuantity,
        decreaseQuantity,
        quantityChanged,
        deleteItem,
        updateSelection,
        toggleSelectAll
      }
    }
  }).mount('#miniCart')
}
</script>
@endpush
