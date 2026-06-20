<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\PointsMall\Controllers\Console\ItemController;
use Plugin\PointsMall\Controllers\Console\RedemptionController;

Route::get('/points-mall/items', [ItemController::class, 'index'])->name('points_mall.items');
Route::get('/points-mall/items/create', [ItemController::class, 'create'])->name('points_mall.items.create');
Route::post('/points-mall/items', [ItemController::class, 'store'])->name('points_mall.items.store');
Route::get('/points-mall/items/{id}/edit', [ItemController::class, 'edit'])->name('points_mall.items.edit');
Route::put('/points-mall/items/{id}', [ItemController::class, 'update'])->name('points_mall.items.update');
Route::delete('/points-mall/items/{id}', [ItemController::class, 'destroy'])->name('points_mall.items.destroy');

Route::get('/points-mall/redemptions', [RedemptionController::class, 'index'])->name('points_mall.redemptions');
Route::put('/points-mall/redemptions/{id}', [RedemptionController::class, 'update'])->name('points_mall.redemptions.update');
