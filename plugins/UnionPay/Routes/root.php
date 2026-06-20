<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\UnionPay\Controllers\UnionPayController;

// 银联异步通知（无 locale 前缀）
Route::match(['get', 'post'], '/callback/union_pay', [UnionPayController::class, 'notify'])->name('union_pay.notify');
