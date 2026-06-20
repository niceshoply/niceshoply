<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Recharge\Controllers\Console\RechargeController;

Route::get('/recharge/plans', [RechargeController::class, 'plans'])->name('recharge.plans');
Route::post('/recharge/plans', [RechargeController::class, 'storePlan'])->name('recharge.plans.store');
Route::put('/recharge/plans/{id}', [RechargeController::class, 'updatePlan'])->name('recharge.plans.update');
Route::delete('/recharge/plans/{id}', [RechargeController::class, 'destroyPlan'])->name('recharge.plans.destroy');
Route::get('/recharge/records', [RechargeController::class, 'records'])->name('recharge.records');
