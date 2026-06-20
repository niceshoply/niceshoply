<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ProductIo\Controllers\Console\ProductIOController;

Route::get('/product-io', [ProductIOController::class, 'index'])->name('product_io.index');
Route::get('/product-io/export', [ProductIOController::class, 'export'])->name('product_io.export');
Route::post('/product-io/import', [ProductIOController::class, 'import'])->name('product_io.import');
