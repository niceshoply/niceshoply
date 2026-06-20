{{--
  ============================================================
  【文件说明】
    用户中心 — 提现申请详情页。
    以信息表格形式展示单条提现申请的完整详情，包括提现金额、提现方式、
    提现账号、银行信息（如果是银行卡）、申请时间、当前状态，
    以及管理员审核备注（如果存在）。
    页面顶部大号状态徽章突出显示当前审核状态。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.wallet.withdrawals.show
    URL 示例：/{locale}/account/wallet/withdrawals/{id}
    控制器：Front\Account\WithdrawalController@show
    路由参数：id — 提现申请记录 ID

  【可用变量】
    $withdrawal — 提现申请模型对象，含：
                    amount              提现金额（数值）
                    account_type_format 提现方式格式化文本
                    account_number      提现账号（完整显示，不脱敏）
                    bank_name           银行名称（可为空，仅银行卡时显示）
                    bank_account        银行账户名（可为空，仅银行卡时显示）
                    status              状态原始值（pending/approved/paid/rejected）
                    status_format       状态格式化文本
                    created_at          申请时间（Carbon）
                    comment             用户申请备注（可为空）
                    admin_comment       管理员审核备注（可为空，存在时以 alert-info 展示）

  【Sections】
    content → 提现详情信息表（无独立的 body-class section，注意与列表页保持一致可添加）

  【插件钩子】
    本页面暂无 @hookinsert 插入点。

  【状态颜色映射】
    pending  → bg-warning + bi-clock 图标
    approved → bg-info + bi-check-circle 图标
    paid     → bg-success + bi-check-circle-fill 图标
    rejected → bg-danger + bi-x-circle 图标

  【自定义建议】
    - 本页面未设置 @section('body-class')，如需页面级样式隔离可添加：
        @section('body-class', 'page-withdrawal-show')
    - 管理员备注（admin_comment）通过 alert alert-info 区块展示，
      可根据状态改变 alert 颜色（如 rejected 时用 alert-danger）
    - 可在详情下方添加"再次申请"按钮（仅当 rejected 状态时显示）
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 提现申请详情页。
    以详情表格形式展示单条提现申请的所有信息：提现金额、收款账户类型、
    账号（完整显示，不脱敏）、银行名称/账户（若有）、申请时间、状态徽章，
    以及申请人备注和管理员备注（审核意见）。
    状态徽章根据 pending/approved/paid/rejected 显示不同颜色。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.wallet.withdrawals.show
    URL 示例：/{locale}/account/wallet/withdrawals/{withdrawal}
    控制器：Front\Account\WithdrawalController@show

  【可用变量】
    $withdrawal — 提现申请对象，含：
                    id                  申请 ID
                    amount              提现金额
                    account_type_format 收款账户类型文本
                    account_number      账号（本页完整显示）
                    bank_name           银行名称（可为空）
                    bank_account        银行账户名（可为空）
                    comment             申请人备注（可为空）
                    admin_comment       管理员审核备注（可为空，拒绝时常用）
                    status              状态原始值
                    status_format       状态格式化文本
                    created_at          申请时间（Carbon）

  【Sections】
    body-class → （未定义，默认无特定 body class）
    content    → 状态徽章 + 详情表格

  【自定义建议】
    - admin_comment 通常在"已拒绝"时由管理员填写，可以蓝色提示框（alert-info）形式展示
    - 可在详情底部添加"撤销申请"按钮（仅 pending 状态时显示）
  ============================================================
--}}
@extends('layouts.app')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.withdrawals.index" title="{{ __('front/withdrawal.withdrawal_detail') }}"/>

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="withdrawal-detail-box bg-white border rounded p-4 mb-4">
          <div class="withdrawal-card-title d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <span class="fw-bold">{{ __('front/withdrawal.withdrawal_detail') }}</span>
            <a href="{{ account_route('wallet.withdrawals.index') }}" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left"></i> {{ __('common/base.back') }}
            </a>
          </div>

          <div class="withdrawal-info mt-3">
            <div class="row mb-4">
              <div class="col-12 text-center">
                <div class="status-badge mb-4 mt-4">
                  @switch($withdrawal->status)
                    @case('pending')
                      <span class="badge bg-warning fs-6 px-3 py-2">
                        <i class="bi bi-clock"></i> {{ $withdrawal->status_format }}
                      </span>
                      @break
                    @case('approved')
                      <span class="badge bg-info fs-6 px-3 py-2">
                        <i class="bi bi-check-circle"></i> {{ $withdrawal->status_format }}
                      </span>
                      @break
                    @case('paid')
                      <span class="badge bg-success fs-6 px-3 py-2">
                        <i class="bi bi-check-circle-fill"></i> {{ $withdrawal->status_format }}
                      </span>
                      @break
                    @case('rejected')
                      <span class="badge bg-danger fs-6 px-3 py-2">
                        <i class="bi bi-x-circle"></i> {{ $withdrawal->status_format }}
                      </span>
                      @break
                    @default
                      <span class="badge bg-secondary fs-6 px-3 py-2">{{ $withdrawal->status_format }}</span>
                  @endswitch
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <table class="table table-borderless withdrawal-detail-table mt-3">
                  <tbody>
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3" style="width: 200px;">{{ __('front/withdrawal.withdrawal_amount') }}</td>
                      <td class="value py-3">
                        <span class="fw-bold text-primary fs-5">{{ currency_format($withdrawal->amount) }}</span>
                      </td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.account_type') }}</td>
                      <td class="value py-3">{{ $withdrawal->account_type_format }}</td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.account_number') }}</td>
                      <td class="value py-3">
                        <code>{{ $withdrawal->account_number }}</code>
                      </td>
                    </tr>
                    @if($withdrawal->bank_name)
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.bank_name') }}</td>
                      <td class="value py-3">{{ $withdrawal->bank_name }}</td>
                    </tr>
                    @endif
                    @if($withdrawal->bank_account)
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.bank_account') }}</td>
                      <td class="value py-3">{{ $withdrawal->bank_account }}</td>
                    </tr>
                    @endif
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.created_at') }}</td>
                      <td class="value py-3">{{ $withdrawal->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.status') }}</td>
                      <td class="value py-3">{{ $withdrawal->status_format }}</td>
                    </tr>
                    @if($withdrawal->comment)
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.comment') }}</td>
                      <td class="value py-3">{{ $withdrawal->comment }}</td>
                    </tr>
                    @endif
                    @if($withdrawal->admin_comment)
                    <tr class="border-bottom">
                      <td class="label fw-semibold text-muted py-3">{{ __('front/withdrawal.admin_comment') }}</td>
                      <td class="value py-3">
                        <div class="alert alert-info mb-0">
                          <i class="bi bi-info-circle"></i>
                          {{ $withdrawal->admin_comment }}
                        </div>
                      </td>
                    </tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection