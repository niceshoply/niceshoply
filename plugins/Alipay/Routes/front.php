<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

use Illuminate\Support\Facades\Route;
use Plugin\Alipay\Controllers\AlipayController;

// 手机 WAP 支付（App WebView 加载此页，自动提交支付宝表单）
Route::get('/orders/{number}/alipay-wap', [AlipayController::class, 'wap'])
    ->name('alipay_wap');
