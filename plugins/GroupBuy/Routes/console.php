<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\GroupBuy\Controllers\Console\GroupBuyActivityController;

Route::get('/group_buy_activities', [GroupBuyActivityController::class, 'index'])->name('group_buy_activities.index');
Route::get('/group_buy_activities/create', [GroupBuyActivityController::class, 'create'])->name('group_buy_activities.create');
Route::post('/group_buy_activities', [GroupBuyActivityController::class, 'store'])->name('group_buy_activities.store');
Route::get('/group_buy_activities/{id}/edit', [GroupBuyActivityController::class, 'edit'])->name('group_buy_activities.edit');
Route::put('/group_buy_activities/{id}', [GroupBuyActivityController::class, 'update'])->name('group_buy_activities.update');
Route::delete('/group_buy_activities/{id}', [GroupBuyActivityController::class, 'destroy'])->name('group_buy_activities.destroy');
Route::get('/group_buy_activities/{id}/groups', [GroupBuyActivityController::class, 'groups'])->name('group_buy_activities.groups');
