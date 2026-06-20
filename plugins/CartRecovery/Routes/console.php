<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\CartRecovery\Controllers\Console\CartRecoveryController;

Route::get('/cart-recovery', [CartRecoveryController::class, 'index'])->name('cart_recovery.index');
Route::post('/cart-recovery/scan', [CartRecoveryController::class, 'scan'])->name('cart_recovery.scan');
