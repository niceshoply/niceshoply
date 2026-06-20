<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\PointsMall\Controllers\Front\PointsMallController;

Route::get('/points-mall', [PointsMallController::class, 'index'])->name('points_mall.list');
Route::get('/points-mall/redemptions', [PointsMallController::class, 'myRedemptions'])->name('points_mall.my');
Route::post('/points-mall/redeem', [PointsMallController::class, 'redeem'])->name('points_mall.redeem');
