<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Recharge\Controllers\Front\RechargeController;

Route::get('/recharge/plans', [RechargeController::class, 'plans'])->name('recharge.plans');
Route::post('/recharge/create', [RechargeController::class, 'create'])->name('recharge.create');
Route::get('/recharge/records', [RechargeController::class, 'myRecords'])->name('recharge.records');
