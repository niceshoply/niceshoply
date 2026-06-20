<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\MultiWarehouse\Controllers\Front\WarehouseController;

Route::get('/warehouse/stock/{skuId}', [WarehouseController::class, 'stock'])->whereNumber('skuId')->name('warehouse.stock');
Route::get('/warehouse/allocate/{skuId}', [WarehouseController::class, 'allocate'])->whereNumber('skuId')->name('warehouse.allocate');
