<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\FlashSale\Controllers\Console\FlashSaleController;

Route::get('/flash_sales', [FlashSaleController::class, 'index'])->name('flash_sales.index');
Route::get('/flash_sales/create', [FlashSaleController::class, 'create'])->name('flash_sales.create');
Route::post('/flash_sales', [FlashSaleController::class, 'store'])->name('flash_sales.store');
Route::get('/flash_sales/{id}/edit', [FlashSaleController::class, 'edit'])->name('flash_sales.edit');
Route::put('/flash_sales/{id}', [FlashSaleController::class, 'update'])->name('flash_sales.update');
Route::delete('/flash_sales/{id}', [FlashSaleController::class, 'destroy'])->name('flash_sales.destroy');
