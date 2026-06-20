{{--
  ============================================================
  【文件说明】
    用户中心 — 提现申请记录列表页。
    以分页表格形式展示当前会员所有提现申请记录，
    每条显示提现金额、提现方式、账号（脱敏显示）、状态、申请时间，
    并提供查看详情的链接。
    页面右上角提供"申请提现"快捷入口。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.wallet.withdrawals.index
    URL 示例：/{locale}/account/wallet/withdrawals
    控制器：Front\Account\WithdrawalController@index

  【可用变量】
    $withdrawals — LengthAwarePaginator，提现申请分页集合，每条含：
                     id                  记录 ID（用于详情路由参数）
                     amount              提现金额（数值）
                     account_type        提现方式原始值（如 'bank'/'alipay'/'paypal'）
                     account_type_format 提现方式格式化文本
                     account_number      提现账号（模板中脱敏处理：前6位+****+后4位）
                     status              状态原始值（pending/approved/paid/rejected）
                     status_format       状态格式化文本
                     created_at          申请时间（Carbon）

  【Sections】
    body-class → 'page-wallet'
    content    → 提现记录表格 + 分页（无数据时显示 x-common-no-data 组件）

  【插件钩子】
    @hookinsert('account.withdrawals_index.top')    — 容器顶部
    @hookinsert('account.withdrawals_index.bottom') — 容器底部

  【状态颜色映射】
    pending  → bg-warning（黄色，待审核）
    approved → bg-info（蓝色，已审核）
    paid     → bg-success（绿色，已打款）
    rejected → bg-danger（红色，已拒绝）
    其他     → bg-secondary（灰色）

  【自定义建议】
    - 账号脱敏逻辑在模板中直接实现（substr 前6 + **** + 后4），
      如需统一处理可封装为 Model Accessor
    - 申请提现入口：account_route('wallet.withdrawals.create')
    - 详情页路由：account_route('wallet.withdrawals.show', $withdrawal->id)
    - 分页使用 $withdrawals->withQueryString()->links(...)，保留过滤参数
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 提现申请列表页。
    以分页表格形式展示该会员所有提现申请记录，
    包含金额、收款账户类型（脱敏显示账号）、状态（带颜色徽章）、
    申请时间及查看详情链接。右上角提供"申请提现"快捷按钮。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.wallet.withdrawals.index
    URL 示例：/{locale}/account/wallet/withdrawals
    控制器：Front\Account\WithdrawalController@index

  【可用变量】
    $withdrawals — LengthAwarePaginator，提现申请集合，每条含：
                     id                  申请 ID
                     amount              提现金额（数值型）
                     account_type_format 收款账户类型文本（如 支付宝/银行卡/PayPal）
                     account_number      账号（模板中脱敏：前6位 + **** + 后4位）
                     status              状态原始值（pending/approved/paid/rejected）
                     status_format       状态格式化文本
                     created_at          申请时间（Carbon）

  【Sections】
    body-class → 'page-wallet'
    content    → 提现列表表格 + 分页

  【插件钩子】
    @hookinsert('account.withdrawals_index.top')    — 内容顶部
    @hookinsert('account.withdrawals_index.bottom') — 内容底部

  【自定义建议】
    - 状态徽章颜色：pending=warning / approved=info / paid=success / rejected=danger
    - 账号脱敏规则可在模板中调整 substr 截取长度
    - currency_format($withdrawal->amount) 按当前货币格式化金额
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-wallet')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.withdrawals.index" title="{{ __('front/withdrawal.my_withdrawals') }}"/>

  @hookinsert('account.withdrawals_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="withdrawal-card-box">
          <div class="withdrawal-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/withdrawal.withdrawal_history') }}</span>
            <a href="{{ account_route('wallet.withdrawals.create') }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle"></i> {{ __('front/withdrawal.apply_withdrawal') }}
            </a>
          </div>

          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-3"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-3"/>
          @endif

          @if ($withdrawals->count())
            <div class="table-responsive">
              <table class="table align-middle withdrawal-table-box table-response">
                <thead>
                <tr>
                  <th class="text-center">{{ __('front/withdrawal.amount') }}</th>
                  <th class="text-center">{{ __('front/withdrawal.account_type') }}</th>
                  <th class="text-center">{{ __('front/withdrawal.account_number') }}</th>
                  <th class="text-center">{{ __('front/withdrawal.status') }}</th>
                  <th class="text-center">{{ __('front/withdrawal.created_at') }}</th>
                  <th class="text-center">{{ __('front/common.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($withdrawals as $withdrawal)
                  <tr>
                    <td class="text-center">
                      <span class="fw-bold text-primary">{{ currency_format($withdrawal->amount) }}</span>
                    </td>
                    <td class="text-center">{{ $withdrawal->account_type_format }}</td>
                    <td class="text-center">
                      <span class="text-muted">{{ substr($withdrawal->account_number, 0, 6) }}****{{ substr($withdrawal->account_number, -4) }}</span>
                    </td>
                    <td class="text-center">
                      @switch($withdrawal->status)
                        @case('pending')
                          <span class="badge bg-warning">{{ $withdrawal->status_format }}</span>
                          @break
                        @case('approved')
                          <span class="badge bg-info">{{ $withdrawal->status_format }}</span>
                          @break
                        @case('paid')
                          <span class="badge bg-success">{{ $withdrawal->status_format }}</span>
                          @break
                        @case('rejected')
                          <span class="badge bg-danger">{{ $withdrawal->status_format }}</span>
                          @break
                        @default
                          <span class="badge bg-secondary">{{ $withdrawal->status_format }}</span>
                      @endswitch
                    </td>
                    <td class="text-center">{{ $withdrawal->created_at->format('Y-m-d H:i') }}</td>
                    <td class="text-center">
                      <a href="{{ account_route('wallet.withdrawals.show', $withdrawal->id) }}" 
                         class="btn btn-outline-primary btn-sm">
                        {{ __('front/common.view') }}
                      </a>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>

            {{ $withdrawals->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data text="{{ __('front/withdrawal.no_withdrawals') }}"/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.withdrawals_index.bottom')

@endsection

 