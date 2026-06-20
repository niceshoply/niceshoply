<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Points\Controllers\Front\PointController;

Route::get('/points/balance', [PointController::class, 'balance'])->name('points.balance');
Route::get('/points/logs', [PointController::class, 'logs'])->name('points.logs');
Route::post('/points/use', [PointController::class, 'use'])->name('points.use');
