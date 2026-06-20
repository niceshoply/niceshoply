<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Bargain\Controllers\Console\BargainActivityController;

Route::get('/bargain_activities', [BargainActivityController::class, 'index'])->name('bargain_activities.index');
Route::get('/bargain_activities/create', [BargainActivityController::class, 'create'])->name('bargain_activities.create');
Route::post('/bargain_activities', [BargainActivityController::class, 'store'])->name('bargain_activities.store');
Route::get('/bargain_activities/{id}/edit', [BargainActivityController::class, 'edit'])->name('bargain_activities.edit');
Route::put('/bargain_activities/{id}', [BargainActivityController::class, 'update'])->name('bargain_activities.update');
Route::delete('/bargain_activities/{id}', [BargainActivityController::class, 'destroy'])->name('bargain_activities.destroy');
