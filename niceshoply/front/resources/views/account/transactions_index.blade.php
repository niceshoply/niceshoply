{{--
  ============================================================
  【文件说明】
    用户中心 — 交易记录列表页。
    以分页表格形式展示当前会员的所有钱包交易流水，
    包括充值、消费抵扣、退款入账、提现扣款等各类交易记录。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.wallet.transactions.index
    URL 示例：/{locale}/account/wallet/transactions
    控制器：Front\Account\TransactionController@index

  【可用变量】
    $transactions — LengthAwarePaginator，交易记录分页集合，每条含：
                      type_format  交易类型格式化文本（如"订单消费""退款""充值"等）
                      amount       交易金额（原始数值，正为入账，负为支出）
                      comment      交易备注（如关联订单号）
                      created_at   交易时间（Carbon / 时间字符串）

  【Sections】
    body-class → 'page-wallet'
    content    → 交易记录表格 + 分页（无数据时显示 x-common-no-data 组件）

  【插件钩子】
    @hookinsert('account.transaction_index.top')    — 容器顶部
    @hookinsert('account.transaction_index.bottom') — 容器底部

  【自定义建议】
    - 当前模板 amount 字段直接输出原始值（未格式化），建议替换为 currency_format($transaction->amount)
    - 创建时间直接输出对象（未 format），如需统一格式可改为 $transaction->created_at->format('Y-m-d H:i')
    - 分页使用 $transactions->withQueryString()->links(...)，保留查询参数（如过滤条件）
    - 若需按交易类型过滤，可在表格上方添加 Tab 导航（参考 order_index.blade.php 实现）
    - 建议对 amount 加颜色区分：正数 text-success，负数 text-danger
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 钱包交易记录列表页。
    以分页表格形式展示该会员所有的钱包交易流水，
    包含交易类型、金额、备注和时间。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.wallet.transactions.index
    URL 示例：/{locale}/account/wallet/transactions
    控制器：Front\Account\TransactionController@index

  【可用变量】
    $transactions — LengthAwarePaginator，交易记录集合，每条含：
                      type_format   交易类型格式化文本（充值/消费/退款/提现等）
                      amount        金额（数值型，正数为收入，负数为支出）
                      comment       备注描述
                      created_at    交易时间

  【Sections】
    body-class → 'page-wallet'
    content    → 交易记录表格 + 分页（withQueryString 保留查询参数）

  【插件钩子】
    @hookinsert('account.transaction_index.top')    — 内容顶部
    @hookinsert('account.transaction_index.bottom') — 内容底部

  【自定义建议】
    - 可在表格增加"类型"过滤下拉框，通过 URL 参数 type 筛选
    - 可给正数金额加上 text-success 绿色，负数加 text-danger 红色（已在 wallet_index 示例中实现）
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-wallet')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.transactions.index" title="{{ __('front/account.transactions') }}"/>

  @hookinsert('account.transaction_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="transaction-card-box">
          <div class="transaction-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/transaction.transaction') }}</span>
          </div>

          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-3"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-3"/>
          @endif

          @if ($transactions->count())
            <div class="table-responsive">
              <table class="table align-middle transaction-table-box table-response">
                <thead>
                <tr>
                  <th class="text-center">{{ __('front/transaction.type') }}</th>
                  <th class="text-center">{{ __('front/transaction.amount') }}</th>
                  <th class="text-center">{{ __('front/transaction.comment') }}</th>
                  <th class="text-center">{{ __('front/common.date') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transactions as $transaction)
                  <tr>
                    <td class="text-center">{{ $transaction->type_format }}</td>
                    <td class="text-center">{{ $transaction->amount }}</td>
                    <td class="text-center">{{ $transaction->comment }}</td>
                    <td class="text-center">{{ $transaction->created_at }}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>

            {{ $transactions->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data text="{{ __('front/transaction.no_transactions') }}"/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.transaction_index.bottom')

@endsection


