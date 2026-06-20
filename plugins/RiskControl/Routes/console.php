<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\RiskControl\Controllers\Console\RiskControlController;

Route::get('/risk-control/events', [RiskControlController::class, 'events'])->name('risk_control.events');
Route::get('/risk-control/blacklist', [RiskControlController::class, 'blacklist'])->name('risk_control.blacklist');
Route::post('/risk-control/blacklist', [RiskControlController::class, 'storeBlacklist'])->name('risk_control.blacklist.store');
Route::delete('/risk-control/blacklist/{id}', [RiskControlController::class, 'destroyBlacklist'])->name('risk_control.blacklist.destroy');
