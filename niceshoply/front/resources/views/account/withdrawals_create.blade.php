{{--
  ============================================================
  【文件说明】
    用户中心 — 申请提现页。
    展示当前可用余额，并提供提现申请表单。
    若已存在待审核的提现申请（$has_pending_withdrawal = true），
    则隐藏表单并显示警告提示，防止重复申请。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET） ：account.wallet.withdrawals.create
    路由名称（POST）：account.wallet.withdrawals.store
    URL 示例：/{locale}/account/wallet/withdrawals/create
    控制器：Front\Account\WithdrawalController@create / @store

  【可用变量】
    $available_balance      — float，当前可提现余额（用于显示和 max 属性限制）
    $has_pending_withdrawal — bool，是否已有待审核申请（true 时显示警告、隐藏表单）
    $account_types          — array，支持的提现方式列表，每项含：
                                value  方式键值（如 'alipay'/'bank'/'paypal'）
                                label  方式显示名称

  【Sections】
    body-class → 'page-wallet'
    content    → 余额展示区 + 提现申请表单（或待审核提示）
    footer     → 账户类型切换联动银行信息显隐 JS（@push）

  【插件钩子】
    @hookinsert('account.withdrawals_create.top')    — 容器顶部
    @hookinsert('account.withdrawals_create.bottom') — 容器底部

  【表单字段】
    amount         — 提现金额（数字，required，最大值 = $available_balance，步进 0.01）
    account_type   — 提现方式（下拉选择，required）
    account_number — 提现账号（文本，required）
    bank_name      — 银行名称（文本，仅 account_type = 'bank' 时显示）
    bank_account   — 银行账户名（文本，仅 account_type = 'bank' 时显示）
    comment        — 申请备注（文本域，可选）

  【自定义建议】
    - 当 account_type 切换为 'bank' 时，.bank-info 区块通过 JS 控制显隐
    - 货币代码通过 current_currency_code() 获取，显示在金额输入框后缀
    - 若需要设置最低提现金额，在 <input min="..."> 中修改，并在控制器 validation 中同步校验
    - 可在余额展示区下方添加提现手续费说明
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 申请提现页。
    展示当前可用余额，并提供提现申请表单：填写提现金额、选择收款账户类型
    （支付宝/银行卡/PayPal 等）、填写账号及备注。
    若当前有待处理的提现申请（$has_pending_withdrawal=true），则隐藏表单并显示提示。
    选择"银行卡"类型时，JS 联动显示"银行名称"和"银行账户"额外字段。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET） ：account.wallet.withdrawals.create
    路由名称（POST）：account.wallet.withdrawals.store
    URL 示例：/{locale}/account/wallet/withdrawals/create
    控制器：Front\Account\WithdrawalController@create / @store

  【可用变量】
    $available_balance    — float，当前可提现余额（作为 max 限制和提示显示）
    $has_pending_withdrawal — bool，是否有待处理提现（true 则禁用表单，显示警告）
    $account_types        — array，支持的收款账户类型列表，每条含 value/label

  【Sections】
    body-class → 'page-wallet'
    content    → 余额概览卡 + 提现表单（或待处理提示）
    footer     → 银行类型联动显示 JS（@push）

  【插件钩子】
    @hookinsert('account.withdrawals_create.top')    — 内容顶部
    @hookinsert('account.withdrawals_create.bottom') — 内容底部

  【表单字段】
    amount         — 提现金额（数值，min=0.01, max=$available_balance）
    account_type   — 收款账户类型（下拉，来自 $account_types）
    account_number — 收款账号
    bank_name      — 银行名称（仅当 account_type=bank 时显示）
    bank_account   — 银行账户名（仅当 account_type=bank 时显示）
    comment        — 备注说明（可选）

  【自定义建议】
    - 可添加手续费计算逻辑（展示"到账金额 = 提现金额 - 手续费"）
    - $account_types 由后台系统设置中的提现方式配置决定
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-wallet')

@section('content')
  <x-front-breadcrumb type="route" value="account.wallet.withdrawals.create" title="{{ __('front/withdrawal.apply_withdrawal') }}"/>

  @hookinsert('account.withdrawals_create.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="withdrawal-create-box">
          <div class="withdrawal-card-title">
            <span class="fw-bold">{{ __('front/withdrawal.apply_withdrawal') }}</span>
          </div>

          @if (session('success'))
            <x-common-alert type="success" msg="{{ session('success') }}" class="mt-3"/>
          @endif
          @if (session('error'))
            <x-common-alert type="danger" msg="{{ session('error') }}" class="mt-3"/>
          @endif

          <!-- Balance information -->
          <div class="wallet-balance-overview">
            <div class="balance-header">
              <i class="bi bi-wallet2"></i>
              <span>{{ __('front/withdrawal.wallet_balance') }}</span>
            </div>
            <div class="balance-content">
              <div class="balance-main">
                <div class="available-balance">
                  <div class="amount">{{ currency_format($available_balance) }}</div>
                  <div class="label">{{ __('front/withdrawal.available_balance') }}</div>
                </div>
              </div>
              <div class="balance-note">
                <i class="bi bi-info-circle"></i>
                <span>{{ __('front/withdrawal.balance_note') }}</span>
              </div>
            </div>
          </div>

          @if($has_pending_withdrawal)
            <div class="alert alert-warning">
              <i class="bi bi-exclamation-triangle"></i>
              {{ __('front/withdrawal.has_pending_withdrawal') }}
            </div>
          @else
            <form action="{{ account_route('wallet.withdrawals.store') }}" method="POST" class="withdrawal-form">
              @csrf
              
              <div class="row">
                <div class="col-12 col-md-6">
                  <div class="mb-3">
                    <label for="amount" class="form-label required">{{ __('front/withdrawal.withdrawal_amount') }}</label>
                    <div class="input-group">
                      <input type="number" 
                             class="form-control @error('amount') is-invalid @enderror" 
                             id="amount" 
                             name="amount" 
                             step="0.01" 
                             min="0.01" 
                             max="{{ $available_balance }}"
                             value="{{ old('amount') }}" 
                             placeholder="{{ __('front/withdrawal.withdrawal_amount') }}" 
                             required>
                      <span class="input-group-text">{{ current_currency_code() }}</span>
                    </div>
                    @error('amount')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">{{ __('front/withdrawal.available_balance') }}: {{ currency_format($available_balance) }}</div>
                  </div>
                </div>

                <div class="col-12 col-md-6">
                  <div class="mb-3">
                    <label for="account_type" class="form-label required">{{ __('front/withdrawal.account_type') }}</label>
                    <select class="form-select @error('account_type') is-invalid @enderror" 
                            id="account_type" 
                            name="account_type" 
                            required>
                      <option value="">{{ __('front/common.please_choose') }}</option>
                      @foreach($account_types as $type)
                        <option value="{{ $type['value'] }}" {{ old('account_type') == $type['value'] ? 'selected' : '' }}>
                          {{ $type['label'] }}
                        </option>
                      @endforeach
                    </select>
                    @error('account_type')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-md-6">
                  <div class="mb-3">
                    <label for="account_number" class="form-label required">{{ __('front/withdrawal.account_number') }}</label>
                    <input type="text" 
                           class="form-control @error('account_number') is-invalid @enderror" 
                           id="account_number" 
                           name="account_number" 
                           value="{{ old('account_number') }}" 
                           placeholder="{{ __('front/withdrawal.account_number') }}" 
                           required>
                    @error('account_number')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>

                <div class="col-12 col-md-6">
                  <div class="mb-3 bank-info" style="display: none;">
                    <label for="bank_name" class="form-label">{{ __('front/withdrawal.bank_name') }}</label>
                    <input type="text" 
                           class="form-control @error('bank_name') is-invalid @enderror" 
                           id="bank_name" 
                           name="bank_name" 
                           value="{{ old('bank_name') }}" 
                           placeholder="{{ __('front/withdrawal.bank_name') }}">
                    @error('bank_name')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <div class="mb-3 bank-info" style="display: none;">
                    <label for="bank_account" class="form-label">{{ __('front/withdrawal.bank_account') }}</label>
                    <input type="text" 
                           class="form-control @error('bank_account') is-invalid @enderror" 
                           id="bank_account" 
                           name="bank_account" 
                           value="{{ old('bank_account') }}" 
                           placeholder="{{ __('front/withdrawal.bank_account') }}">
                    @error('bank_account')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  <div class="mb-3">
                    <label for="comment" class="form-label">{{ __('front/withdrawal.comment') }}</label>
                    <textarea class="form-control @error('comment') is-invalid @enderror" 
                              id="comment" 
                              name="comment" 
                              rows="3" 
                              placeholder="{{ __('front/withdrawal.comment') }}">{{ old('comment') }}</textarea>
                    @error('comment')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-check-circle"></i> {{ __('front/withdrawal.submit_application') }}
                </button>
                <a href="{{ account_route('wallet.withdrawals.index') }}" class="btn btn-secondary ms-2">
                  {{ __('front/common.cancel') }}
                </a>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.withdrawals_create.bottom')

@endsection



@push('footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const accountTypeSelect = document.getElementById('account_type');
  const bankInfoElements = document.querySelectorAll('.bank-info');
  
  function toggleBankInfo() {
    const isBank = accountTypeSelect.value === 'bank';
    bankInfoElements.forEach(element => {
      element.style.display = isBank ? 'block' : 'none';
    });
  }
  
  accountTypeSelect.addEventListener('change', toggleBankInfo);
  
  // Initialize display state
  toggleBankInfo();
});
</script>
@endpush