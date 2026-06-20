{{--
  ============================================================
  【文件说明】
    用户中心首页（账户概览页）。
    展示会员欢迎信息、统计数据（订单数/收藏数/地址数），
    以及最近订单列表（最多显示若干条）。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。
    未登录用户将被重定向到登录页。

  【对应路由/控制器】
    路由名称：account.index
    URL 示例：/{locale}/account
    控制器：Front\AccountController@index（或同名方法）

  【可用变量】
    $customer        — 当前登录会员对象（Model），含 name、email、avatar 等字段
    $order_total     — int，该会员的订单总数
    $fav_total       — int，该会员的收藏商品总数
    $address_total   — int，该会员的收货地址总数
    $latest_orders   — Collection，最近若干条订单对象，每条含以下属性：
                         number           订单编号（字符串）
                         created_at       创建时间（Carbon）
                         billing_method_name 支付方式名称
                         status           订单状态原始值（字符串）
                         status_format    订单状态格式化文本
                         total            订单总金额（已格式化字符串）

  【Sections】
    body-class → 'page-account'（用于页面级 CSS 作用域）
    content    → 页面主体内容

  【插件钩子】
    @hookinsert('account.home.top')          — 页面顶部插入点（面包屑下方、容器外）
    @hookinsert('account.home.info.after')   — 会员信息标题行之后，统计数据之前
    @hookinsert('account.home.analysis.after')— 统计数据区块之后，最近订单标题之前
    @hookinsert('account.home.bottom')       — 页面底部插入点（容器外）
    @hookupdate('account.home.edit_profile') — 编辑资料链接区域（可替换整段）

  【自定义建议】
    - 可在 $latest_orders 循环内添加自定义列（如配送状态、物流单号）
    - 统计卡片可扩展为显示钱包余额：current_customer()->balance
    - 如需展示头像，使用 image_origin($customer->avatar) 生成完整 URL
    - 使用 account_route('orders.index') 可跳转至完整订单列表页
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-account')

@section('content')
  <x-front-breadcrumb type="route" value="account.index" title="{{ __('front/account.account') }}"/>

  @hookinsert('account.home.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box account-info">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.hello') }}, {{ $customer->name }}</span>
            @hookupdate('account.home.edit_profile')
            <a href="{{ account_route('edit.index') }}" class="text-secondary">{{ __('front/account.edit') }} <i
                  class="bi bi-arrow-right"></i></a>
            @endhookupdate
          </div>

          @hookinsert('account.home.info.after')
          
          <div class="account-data">
            <div class="row">
              <div class="col-6 col-md-4">
                <div class="account-item-data">
                  <div class="value">{{ $order_total }}</div>
                  <div class="title text-secondary">{{ __('front/account.orders') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="account-item-data">
                  <div class="value">{{ $fav_total }}</div>
                  <div class="title text-secondary">{{ __('front/account.favorites') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="account-item-data">
                  <div class="value">{{ $address_total }}</div>
                  <div class="title text-secondary">{{ __('front/account.addresses') }}</div>
                </div>
              </div>
            </div>
          </div>

          @hookinsert('account.home.analysis.after')

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.orders') }}</span>
            <a href="{{ account_route('orders.index') }}" class="text-secondary">{{ __('front/account.view_all') }} <i
                  class="bi bi-arrow-right"></i></a>
          </div>

          @if($latest_orders->count())
            <table class="table align-middle account-table-box table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_number') }}</th>
                <th>{{ __('front/order.order_date') }}</th>
                <th>{{ __('front/order.order_billing') }}</th>
                <th>{{ __('front/order.order_status') }}</th>
                <th>{{ __('front/order.order_total') }}</th>
                <th>{{ __('front/common.action') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach($latest_orders as $order)
                <tr>
                  <td data-title="Order ID">{{ $order->number }}</td>
                  <td data-title="Date">{{ $order->created_at->format('Y-m-d') }}</td>
                  <td data-title="Billing">{{ $order->billing_method_name }}</td>
                  <td data-title="Status">
                    <span class="badge {{ $order->status == 'completed' || $order->status == 'paid' ? 'bg-success' : 'bg-warning' }} ">{{ $order->status_format }}</span>
                  </td>
                  <td data-title="Total">{{ $order->total }}</td>
                  <td data-title="Actions">
                    <a href="{{ account_route('orders.number_show', $order->number) }}" class="btn btn-primary btn-sm" role="button">
                        {{ __('front/common.view') }}
                    </a>
                 </td>
                </tr>
              @endforeach
              </tbody>
            </table>
          @else
            <div class="no-order alert">
              <a href="{{ front_route('products.index') }}">
                <i class="bi bi-check-lg"></i>
                {{ __('front/account.no_order') }}
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.home.bottom')

@endsection
