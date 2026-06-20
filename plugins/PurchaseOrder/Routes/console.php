<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\PurchaseOrder\Controllers\Console\PurchaseOrderController;

Route::get('/purchase-order', [PurchaseOrderController::class, 'index'])->name('purchase_order.index');
Route::post('/purchase-order/supplier', [PurchaseOrderController::class, 'storeSupplier'])->name('purchase_order.supplier.store');
Route::post('/purchase-order', [PurchaseOrderController::class, 'storeOrder'])->name('purchase_order.store');
Route::post('/purchase-order/{id}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase_order.receive');
