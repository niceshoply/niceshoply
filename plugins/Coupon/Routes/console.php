<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Coupon\Controllers\Console\CouponController;

Route::get('/coupons', [CouponController::class, 'index'])->name('coupons.index');
Route::get('/coupons/create', [CouponController::class, 'create'])->name('coupons.create');
Route::post('/coupons', [CouponController::class, 'store'])->name('coupons.store');
Route::get('/coupons/{id}/edit', [CouponController::class, 'edit'])->name('coupons.edit');
Route::put('/coupons/{id}', [CouponController::class, 'update'])->name('coupons.update');
Route::delete('/coupons/{id}', [CouponController::class, 'destroy'])->name('coupons.destroy');
