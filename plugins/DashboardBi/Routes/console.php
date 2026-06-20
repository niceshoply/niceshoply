<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\DashboardBi\Controllers\Console\DashboardController;

Route::get('/dashboard-bi', [DashboardController::class, 'index'])->name('dashboard_bi.index');
Route::get('/dashboard-bi/data', [DashboardController::class, 'data'])->name('dashboard_bi.data');
