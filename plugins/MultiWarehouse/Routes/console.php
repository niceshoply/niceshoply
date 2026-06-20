<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\MultiWarehouse\Controllers\Console\MultiWarehouseController;

Route::get('/multi-warehouse', [MultiWarehouseController::class, 'index'])->name('multi_warehouse.index');
Route::post('/multi-warehouse/warehouse', [MultiWarehouseController::class, 'storeWarehouse'])->name('multi_warehouse.warehouse.store');
Route::get('/multi-warehouse/stock', [MultiWarehouseController::class, 'stock'])->name('multi_warehouse.stock');
Route::post('/multi-warehouse/stock', [MultiWarehouseController::class, 'setStock'])->name('multi_warehouse.stock.set');
Route::post('/multi-warehouse/transfer', [MultiWarehouseController::class, 'transfer'])->name('multi_warehouse.transfer');
Route::post('/multi-warehouse/sync', [MultiWarehouseController::class, 'syncAll'])->name('multi_warehouse.sync');
