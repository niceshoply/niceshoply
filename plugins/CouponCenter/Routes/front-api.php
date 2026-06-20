<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\CouponCenter\Controllers\Front\CouponCenterController;

Route::get('/coupon-center/list', [CouponCenterController::class, 'list'])->name('coupon_center.list');
Route::post('/coupon-center/claim', [CouponCenterController::class, 'claim'])->name('coupon_center.claim');
Route::get('/coupon-center/mine', [CouponCenterController::class, 'mine'])->name('coupon_center.mine');
