<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\OfflineRedeem\Controllers\Front\RedeemController;

Route::post('/redeem/verify', [RedeemController::class, 'verify'])->name('offline_redeem.verify');
Route::post('/redeem/use', [RedeemController::class, 'redeem'])->name('offline_redeem.redeem');
