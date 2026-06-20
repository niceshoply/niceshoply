{{--
  ============================================================
  【文件说明】
    用户中心 — 我的钱包概览页。
    分三个区块展示：
    1. 余额概览（总余额 / 冻结余额 / 可用余额）+ 申请提现入口
    2. 提现统计（待审核/已审核/已打款/已拒绝数量）
    3. 最近交易记录（最近若干条，带"查看全部"跳转链接）

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.wallet.index
    URL 示例：/{locale}/account/wallet
    控制器：Front\Account\WalletController@index

  【可用变量】
    $balance               — float/string，钱包总余额
    $freeze_balance        — float/string，冻结余额（提现审核中等情况）
    $available_balance     — float/string，可用余额（= 总余额 - 冻结余额）
    $has_pending_withdrawal— bool，是否存在待审核的提现申请（存在时禁用申请按钮）
    $withdrawal_stats      — array，提现状态统计，键为状态名（pending/approved/paid/rejected），
                             值为对应数量
    $recent_transactions   — Collection，最近若干条交易记录，每条含：
                               type_format  交易类型格式化文本
                               amount       金额（正为收入，负为支出）
                               comment      交易备注
                               created_at   交易时间（Carbon）

  【Sections】
    body-class → 'page-wallet'
    content    → 余额卡片 + 提现统计卡片 + 近期交易记录卡片

  【插件钩子】
    @hookinsert('account.wallet_index.top')    — 容器顶部
    @hookinsert('account.wallet_index.bottom') — 容器底部

  【自定义建议】
    - 余额格式化使用 currency_format($balance)，货币符号由系统设置决定
    - 交易金额正负号通过 $transaction->amount > 0 判断，可自定义颜色：
        正数（收入）→ text-success，负数（支出）→ text-danger
    - 若需隐藏提现功能，可在系统设置中关闭，并在此处通过 $has_pending_withdrawal 
      或自定义条件控制按钮显隐
    - 提现申请入口：account_route('wallet.withdrawals.create')
    - 完整交易记录：account_route('wallet.transactions.index')
    - 完整提现记录：account_route('wallet.withdrawals.index')
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 钱包总览页。
    展示会员钱包的三块余额（总余额/冻结余额/可用余额）、
    提现统计（待处理/已批准/已到账/已拒绝数量）以及最近交易记录摘要。
    提供"申请提现"快捷入口（有待处理提现时禁用）。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.wallet.index
    URL 示例：/{locale}/account/wallet
    控制器：Front\Account\WalletController@index

  【可用变量】
    $balance              — float，账户总余额
    $freeze_balance       — float，冻结中余额（不可使用）
    $available_balance    — float，可用余额（balance - freeze_balance）
    $has_pending_withdrawal — bool，是否存在待处理的提现申请（true 则禁用申请按钮）
    $withdrawal_stats     — array，提现统计：
                              pending / approved / paid / rejected 各状态数量
    $recent_transactions  — Collection，最近 N 条交易记录，每条含：
                              type_format   交易类型文本（充值/消费/退款等）
                              amount        金额（正数为收入，负数为支出）
                              comment       备注
                              created_at    时间（Carbon）

  【Sections】
    body-class → 'page-wallet'
    content    → 余额卡 + 提现统计卡 + 最近交易卡

  【插件钩子】
    @hookinsert('account.wallet_index.top')    — 内容顶部
    @hookinsert('account.wallet_index.bottom') — 内容底部

  【自定义建议】
    - currency_format($amount) 按当前货币格式化金额
    - 可在余额卡下方添加"充值"按钮（需配合充值插件）
    - 最近交易记录默认显示条数由控制器决定，可在控制器中调整
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-wallet')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.index" title="{{ __('front/account.wallet') }}"/>

  @hookinsert('account.wallet_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <!-- 余额概览 -->
        <div class="wallet-card-box wallet-balance">
          <div class="wallet-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.balance_overview') }}</span>
          </div>
          <div class="wallet-balance-data">
            <div class="row">
              <div class="col-6 col-md-4">
                <div class="wallet-balance-item">
                  <div class="value text-primary">{{ currency_format($balance) }}</div>
                  <div class="title text-secondary">{{ __('front/transaction.total') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="wallet-balance-item">
                  <div class="value text-warning">{{ currency_format($freeze_balance) }}</div>
                  <div class="title text-secondary">{{ __('front/transaction.frozen') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-4">
                <div class="wallet-balance-item">
                  <div class="value text-success">{{ currency_format($available_balance) }}</div>
                  <div class="title text-secondary">{{ __('front/transaction.available') }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="wallet-actions mt-3">
            <a href="{{ account_route('wallet.withdrawals.create') }}" 
               class="btn btn-primary {{ $has_pending_withdrawal ? 'disabled' : '' }}">
              <i class="bi bi-cash-coin"></i> {{ __('front/withdrawal.apply_withdrawal') }}
            </a>
            @if($has_pending_withdrawal)
              <small class="text-warning ms-2">{{ __('front/withdrawal.has_pending_withdrawal') }}</small>
            @endif
          </div>
        </div>

        <!-- 提现统计 -->
        <div class="wallet-card-box wallet-withdrawals mt-4">
          <div class="wallet-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/withdrawal.withdrawal_info') }}</span>
            <a href="{{ account_route('wallet.withdrawals.index') }}" class="text-secondary">
              {{ __('front/account.view_all') }} <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          <div class="wallet-withdrawal-stats">
            <div class="row">
              <div class="col-6 col-md-3">
                <div class="wallet-stats-item">
                  <div class="value text-warning">{{ $withdrawal_stats['pending'] }}</div>
                  <div class="title text-secondary">{{ __('front/withdrawal.pending') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="wallet-stats-item">
                  <div class="value text-info">{{ $withdrawal_stats['approved'] }}</div>
                  <div class="title text-secondary">{{ __('front/withdrawal.approved') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="wallet-stats-item">
                  <div class="value text-success">{{ $withdrawal_stats['paid'] }}</div>
                  <div class="title text-secondary">{{ __('front/withdrawal.paid') }}</div>
                </div>
              </div>
              <div class="col-6 col-md-3">
                <div class="wallet-stats-item">
                  <div class="value text-danger">{{ $withdrawal_stats['rejected'] }}</div>
                  <div class="title text-secondary">{{ __('front/withdrawal.rejected') }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 最近交易记录 -->
        <div class="wallet-card-box wallet-transactions mt-4">
          <div class="wallet-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/account.transactions') }}</span>
            <a href="{{ account_route('wallet.transactions.index') }}" class="text-secondary">
              {{ __('front/account.view_all') }} <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          @if ($recent_transactions->count())
            <div class="table-responsive">
              <table class="table align-middle wallet-table-box table-response">
                <thead>
                <tr>
                  <th class="text-center">{{ __('front/transaction.type') }}</th>
                  <th class="text-center">{{ __('front/transaction.amount') }}</th>
                  <th class="text-center">{{ __('front/transaction.comment') }}</th>
                  <th class="text-center">{{ __('front/common.date') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($recent_transactions as $transaction)
                  <tr>
                    <td class="text-center">{{ $transaction->type_format }}</td>
                    <td class="text-center {{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                      {{ $transaction->amount > 0 ? '+' : '' }}{{ currency_format($transaction->amount) }}
                    </td>
                    <td class="text-center">{{ $transaction->comment }}</td>
                    <td class="text-center">{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
          @else
            <x-common-no-data text="{{ __('front/transaction.no_transactions') }}"/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.wallet_index.bottom')

@endsection

 