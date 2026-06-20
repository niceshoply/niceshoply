<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\OfflineRedeem\Controllers\Console\OfflineRedeemController;

Route::get('/offline-redeem', [OfflineRedeemController::class, 'index'])->name('offline_redeem.index');
Route::post('/offline-redeem/generate', [OfflineRedeemController::class, 'generate'])->name('offline_redeem.generate');
