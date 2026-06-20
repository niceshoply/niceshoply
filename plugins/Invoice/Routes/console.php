<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Invoice\Controllers\Console\InvoiceController;

Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::post('/invoices/{id}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue');
Route::post('/invoices/{id}/reject', [InvoiceController::class, 'reject'])->name('invoices.reject');
