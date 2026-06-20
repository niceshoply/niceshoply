<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Presale\Controllers\Console\PresaleController;

Route::get('/presale', [PresaleController::class, 'index'])->name('presale.index');
Route::get('/presale/create', [PresaleController::class, 'create'])->name('presale.create');
Route::post('/presale', [PresaleController::class, 'store'])->name('presale.store');
Route::get('/presale/{id}/edit', [PresaleController::class, 'edit'])->name('presale.edit');
Route::put('/presale/{id}', [PresaleController::class, 'update'])->name('presale.update');
Route::delete('/presale/{id}', [PresaleController::class, 'destroy'])->name('presale.destroy');
