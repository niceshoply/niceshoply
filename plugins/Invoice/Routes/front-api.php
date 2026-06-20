<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Invoice\Controllers\Front\InvoiceController;

Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.list');
Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
