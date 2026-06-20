{{--
================================================================================
【文件说明】
  用户中心侧边栏导航局部模板 —— 显示在用户中心所有页面（账户概览、订单列表、
  收藏夹、钱包、评价、地址管理、退货、账号编辑、修改密码）的左侧。
  包含当前用户头像、姓名、邮箱，以及各功能模块的导航链接（当前页高亮）。

【引用方式】
  @include('shared.account-sidebar')
  ※ 通常在用户中心的 layout 文件中引用，无需手动传参。
     $customer 由用户中心中间件自动注入到视图共享数据中。

【可用变量】
  全局注入（由中间件或 View::share 共享）：
    $customer               — 当前登录会员模型对象，包含：
      ->avatar              — 头像图片路径（传入 image_resize() 生成缩略图 URL）
      ->name                — 会员姓名
      ->email               — 会员邮箱

  全局辅助函数：
    image_resize($path)         — 生成头像缩略图 URL（使用默认尺寸）
    account_route('name')       — 生成用户中心路由 URL，如 account_route('orders.index')
    equal_account_route_name('name') — 判断当前路由名称是否匹配，用于设置 active 高亮
    front_trans('account.xxx')  — 获取用户中心多语言翻译文字

  可用路由名称（传入 account_route / equal_account_route_name）：
    'index'                     — 账户首页（概览）
    'orders.index'              — 订单列表
    'orders.number_show'        — 订单详情（按订单号）
    'favorites.index'           — 收藏夹
    'wallet.index'              — 钱包首页
    'wallet.transactions.index' — 钱包流水记录
    'wallet.withdrawals.index'  — 提现列表
    'wallet.withdrawals.create' — 发起提现
    'wallet.withdrawals.show'   — 提现详情
    'reviews.index'             — 我的评价
    'addresses.index'           — 地址管理
    'order_returns.index'       — 退货申请列表
    'order_returns.create'      — 发起退货
    'order_returns.show'        — 退货详情
    'edit.index'                — 编辑账号信息
    'password.index'            — 修改密码
    'logout'                    — 退出登录

【输出内容】
  .account-sidebar 容器，包含：
  - 用户信息区：头像图片 + 欢迎语（姓名） + 邮箱
  - <ul class="account-links"> 导航菜单：
    账户、订单、收藏、钱包、评价、地址、退货、编辑资料、修改密码、退出登录

【Hook 扩展点】
  @hookinsert('front.account.sidebar.avatar.after', $customer) — 头像区域之后
  @hookinsert('front.account.sidebar.home.after')     — 账户首页菜单项之后
  @hookinsert('front.account.sidebar.orders.after')   — 订单菜单项之后
  @hookinsert('front.account.sidebar.favorites.after')— 收藏菜单项之后
  @hookinsert('front.account.sidebar.wallet.after')   — 钱包菜单项之后
  @hookinsert('front.account.sidebar.reviews.after')  — 评价菜单项之后
  @hookinsert('front.account.sidebar.addresses.after')— 地址菜单项之后
  @hookinsert('front.account.sidebar.order_returns.after') — 退货菜单项之后
  @hookinsert('front.account.sidebar.edit.after')     — 编辑资料菜单项之后
  @hookinsert('front.account.sidebar.password.after') — 修改密码菜单项之后

【自定义建议】
  1. 新增菜单项时，在对应 @hookinsert 位置注入即可，无需修改本模板。
  2. 高亮判断使用 equal_account_route_name()，支持传入字符串或数组（任一匹配则高亮）。
  3. 如需在侧边栏显示统计数字（如"待处理订单 3"），
     可在控制器中将统计数据通过 View::share 共享，然后在对应 <li> 中渲染。
  4. 头像默认使用 image_resize() 不传尺寸参数，如需固定头像尺寸可传入 width/height。
================================================================================
--}}
<div class="account-sidebar">
  <div class="account-user flex-column">
    <div class="profile"><img src="{{ image_resize($customer->avatar) }}" class="img-fluid"></div>
    <div class="account-name">
      <div class="fw-bold name">{{ __('front/account.hello') }}, {{ $customer->name }}</div>
      <div class="text-secondary email">{{ $customer->email }}</div>
    </div>
    @hookinsert('front.account.sidebar.avatar.after', $customer)
  </div>

  <ul class="account-links">
    <li class="{{ equal_account_route_name('index') ? 'active' : '' }}">
      <a href="{{ account_route('index') }}"><i class="bi bi-person"></i>{{ front_trans('account.account') }}</a>
    </li>
    @hookinsert('front.account.sidebar.home.after')

    <li class="{{ equal_account_route_name(['orders.index', 'orders.number_show']) ? 'active' : '' }}">
      <a href="{{ account_route('orders.index') }}"><i
            class="bi bi-clipboard2-check"></i>{{ front_trans('account.orders') }}</a>
    </li>
    @hookinsert('front.account.sidebar.orders.after')

    <li class="{{ equal_account_route_name('favorites.index') ? 'active' : '' }}">
      <a href="{{ account_route('favorites.index') }}"><i class="bi bi-heart"></i>{{ front_trans('account.favorites') }}
      </a>
    </li>
    @hookinsert('front.account.sidebar.favorites.after')
    
    <li class="{{ equal_account_route_name(['wallet.index', 'wallet.transactions.index', 'wallet.withdrawals.index', 'wallet.withdrawals.create', 'wallet.withdrawals.show']) ? 'active' : '' }}">
      <a href="{{ account_route('wallet.index') }}"><i class="bi bi-wallet"></i>{{ front_trans('account.wallet') }}
      </a>
    </li>
    @hookinsert('front.account.sidebar.wallet.after')
    
    <li class="{{ equal_account_route_name('reviews.index') ? 'active' : '' }}">
      <a href="{{ account_route('reviews.index') }}"><i class="bi bi-chat-dots"></i>{{ front_trans('account.reviews') }}
      </a>
    </li>
    @hookinsert('front.account.sidebar.reviews.after')

    <li class="{{ equal_account_route_name('addresses.index') ? 'active' : '' }}">
      <a href="{{ account_route('addresses.index') }}"><i
            class="bi bi-geo-alt"></i>{{ front_trans('account.addresses') }}</a>
    </li>
    @hookinsert('front.account.sidebar.addresses.after')

    <li class="{{ equal_account_route_name(['order_returns.index', 'order_returns.create', 'order_returns.show']) ? 'active' : '' }}">
      <a href="{{ account_route('order_returns.index') }}"><i
            class="bi bi-backpack"></i>{{ front_trans('account.order_returns') }}</a>
    </li>
    @hookinsert('front.account.sidebar.order_returns.after')

    <li class="{{ equal_account_route_name('privacy.index') ? 'active' : '' }}">
      <a href="{{ account_route('privacy.index') }}"><i class="bi bi-shield-check"></i>{{ front_trans('privacy.title') }}</a>
    </li>
    @hookinsert('front.account.sidebar.privacy.after')

    <li class="{{ equal_account_route_name('edit.index') ? 'active' : '' }}">
      <a href="{{ account_route('edit.index') }}"><i class="bi bi-pen"></i>{{ front_trans('account.edit') }}</a>
    </li>
    @hookinsert('front.account.sidebar.edit.after')

    <li class="{{ equal_account_route_name('password.index') ? 'active' : '' }}">
      <a href="{{ account_route('password.index') }}"><i
            class="bi bi-shield-lock"></i>{{ front_trans('account.password') }}</a>
    </li>
    @hookinsert('front.account.sidebar.password.after')

    <li><a href="{{ account_route('logout') }}"><i class="bi bi-box-arrow-left"></i>{{ front_trans('account.logout') }}</a></li>
  </ul>
</div>
