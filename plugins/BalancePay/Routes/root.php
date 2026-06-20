<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\BalancePay\Controllers\BalancePayController;

// 余额支付确认（front 中间件，含会话与 current_customer）
Route::post('/payment/balance-pay/confirm', [BalancePayController::class, 'confirm'])->name('balance_pay.confirm');
