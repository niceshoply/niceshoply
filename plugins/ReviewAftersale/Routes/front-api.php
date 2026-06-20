<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ReviewAftersale\Controllers\Front\AftersaleController;
use Plugin\ReviewAftersale\Controllers\Front\ReviewController;

// 评价（列表公开，提交需登录由中间件控制）
Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.list');
Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

// 售后
Route::get('/aftersales', [AftersaleController::class, 'index'])->name('aftersales.list');
Route::get('/aftersales/{id}', [AftersaleController::class, 'show'])->name('aftersales.show');
Route::post('/aftersales', [AftersaleController::class, 'store'])->name('aftersales.store');
