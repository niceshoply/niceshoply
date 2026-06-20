<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\BankTransfer\Controllers\ReceiptController;

// 银行转账回单上传端点：
// 必须经 jwt.auth 鉴权（仅登录客户可上传），控制器内再校验订单归属，
// 双重防护，杜绝任意用户对任意订单号上传回单（IDOR / 未授权写入）。
Route::middleware('jwt.auth')->group(function () {
    Route::post('/orders/{number}/receipt', [ReceiptController::class, 'upload'])->name('orders.receipt_upload');
});
