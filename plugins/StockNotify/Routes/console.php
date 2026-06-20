<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\StockNotify\Controllers\Console\StockNotifyController;

Route::get('/stock-notify', [StockNotifyController::class, 'index'])->name('stock_notify.index');
Route::post('/stock-notify/scan', [StockNotifyController::class, 'scan'])->name('stock_notify.scan');
