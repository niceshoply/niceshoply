<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\StorePickup\Controllers\Console\StoreController;

Route::get('/store-pickup', [StoreController::class, 'index'])->name('store_pickup.index');
Route::post('/store-pickup', [StoreController::class, 'store'])->name('store_pickup.store');
Route::put('/store-pickup/{id}', [StoreController::class, 'update'])->name('store_pickup.update');
Route::delete('/store-pickup/{id}', [StoreController::class, 'destroy'])->name('store_pickup.destroy');
