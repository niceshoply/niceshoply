<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ReviewAftersale\Controllers\Console\AftersaleController;
use Plugin\ReviewAftersale\Controllers\Console\ReviewController;

Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
Route::post('/reviews/{id}/audit', [ReviewController::class, 'audit'])->name('reviews.audit');
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

Route::get('/aftersales', [AftersaleController::class, 'index'])->name('aftersales.index');
Route::put('/aftersales/{id}', [AftersaleController::class, 'update'])->name('aftersales.update');
