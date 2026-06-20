<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Paypal\Controllers\PaypalController;

// 创建 PayPal 订单（前端拿到 approve_url 后跳转 PayPal 授权）
Route::post('/paypal/create-order', [PaypalController::class, 'createOrder'])->name('paypal_create_order');

// PayPal 授权成功 / 取消后的浏览器回跳
Route::get('/paypal/return', [PaypalController::class, 'paypalReturn'])->name('paypal_return');
Route::get('/paypal/cancel', [PaypalController::class, 'paypalCancel'])->name('paypal_cancel');

// PayPal Webhook 异步回调（需在 PayPal 后台配置此地址）
Route::post('/callback/paypal', [PaypalController::class, 'callback'])->name('paypal_callback');
