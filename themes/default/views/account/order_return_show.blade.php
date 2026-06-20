{{--
  ============================================================
  【文件说明】
    用户中心 — 退货/退款申请详情页（RMA 详情）。
    展示单条退货/退款申请的完整信息，包括申请单编号、关联订单、商品名称、
    是否已拆封、退货说明、当前状态、申请时间，以及该申请的处理历史记录流水。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.order_returns.show
    URL 示例：/{locale}/account/order-returns/{order_return}
    控制器：Front\Account\OrderReturnController@show
    路由参数：order_return — 申请记录 ID

  【可用变量】
    $order_return — 退货申请模型对象，含：
                      number        申请单编号
                      order_number  关联订单编号
                      product_id    商品 ID
                      product_name  商品名称
                      quantity      申请退货数量
                      opened        是否已拆封（boolean）
                      comment       用户备注/说明
                      status_format 当前状态格式化文本
                      updated_at    最后更新时间（展示为申请时间）
    $histories    — Collection，申请状态变更历史记录集合，每条含：
                      status_format 状态格式化文本
                      comment       操作备注（管理员填写）
                      created_at    变更时间

  【Sections】
    body-class → 'page-order'
    content    → 退货详情信息表 + 状态流水历史表

  【插件钩子】
    @hookinsert('account.order_return_create.top')    — 容器顶部（复用了 create 的插入点名称）
    @hookinsert('account.order_return_create.bottom') — 容器底部（复用了 create 的插入点名称）

  【自定义建议】
    - 若需展示退货凭证图片，可在详情表中追加对应字段行
    - 如需显示退款金额，可在 $order_return 中添加 refund_amount 字段并在此处渲染
    - 历史记录为空时无 else 分支，建议添加"暂无处理记录"提示
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 退货申请详情页。
    展示单条退货申请的完整信息：申请编号、关联订单号、商品ID、
    是否已拆封、退货备注、当前状态、创建时间、商品名、退货数量，
    以及状态变更历史记录（timeline 形式）。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.order_returns.show
    URL 示例：/{locale}/account/order-returns/{order_return}
    控制器：Front\Account\OrderReturnController@show

  【可用变量】
    $order_return — 退货申请对象，含：
                      number         申请编号
                      order_number   关联订单编号
                      product_id     商品 ID
                      product_name   商品名称
                      quantity       退货数量
                      opened         是否已拆封（bool）
                      comment        退货备注
                      status_format  状态格式化文本
                      updated_at     最后更新时间
    $histories    — 状态变更历史集合，每条含：
                      status_format  状态文本
                      comment        备注
                      created_at     变更时间

  【Sections】
    body-class → 'page-order'
    content    → 申请详情表格 + 状态历史表格

  【插件钩子】
    @hookinsert('account.order_return_create.top')    — 内容顶部
    @hookinsert('account.order_return_create.bottom') — 内容底部

  【自定义建议】
    - 历史记录可改为时间轴（timeline）样式展示，更直观
    - 可根据 $order_return->status 显示不同的操作按钮（如已批准时显示寄回物流填写）
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="order_return" :value="$order_return"/>

  @hookinsert('account.order_return_create.top')
  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>

      <div class="col-12 col-lg-9">
        <div class="account-card-box order-box">
          @if (session()->has('errors'))
            <x-common-alert type="danger" msg="{{ session('errors')->first() }}" class="mt-4"/>
          @endif
          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-4"/>
          @endif

          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.order_returns') }}</span>
            <span class="fs-6">{{ $order_return->number }}</span>
          </div>

          <table class="table table-bordered">
            <tbody>           
            <tr>
              <td class="order_return">{{ __('front/return.number') }}:</td>
              <td class="order_return">{{ $order_return->number }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.order_number') }}:</td>
              <td class="order_return">{{ $order_return->order_number }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.product_id') }}:</td>
              <td class="order_return">{{ $order_return->product_id }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.opened') }}:</td>
              <td class="order_return">{{ $order_return->opened ? __('front/common.yes') : __('front/common.no') }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.comment') }}:</td>
              <td class="order_return">{{ $order_return->comment }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.status') }}:</td>
              <td class="order_return">{{ $order_return->status_format }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.created_at') }}:</td>
              <td class="order_return">{{ $order_return->updated_at }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.product_name') }}:</td>
              <td class="order_return">{{ $order_return->product_name }}</td>
            </tr>
            <tr>
              <td class="order_return">{{ __('front/return.quantity') }}:</td>
              <td class="order_return">{{ $order_return->quantity }}</td>
            </tr>
            </tbody>
          </table>

          <div class="table-responsive mt-5">
            <div class="account-card-title d-flex justify-content-between align-items-center">
              <span class="fw-bold">{{ __('console/order.history') }}</span>
            </div>
            <table class="table table-bordered">
              <thead>
              <tr>
                <th class="order_return">{{ __('front/order.order_status') }}</th>
                <th class="order_return">{{ __('front/order.remark') }}</th>
                <th class="order_return">{{ __('front/order.order_date') }}</th>
              </tr>
              </thead>

              @if($histories->count())
                <tbody>
                @foreach($histories as $history)
                  <tr>
                    <td class="order_return">{{ $history->status_format }}</td>
                    <td class="order_return">{{ $history->comment }}</td>
                    <td class="order_return">{{ $history->created_at }}</td>
                  </tr>
                @endforeach
                </tbody>
              @endif
            </table>
          </div>
        </div>
      </div>
    </div>

    @hookinsert('account.order_return_create.bottom')
@endsection