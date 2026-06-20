<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\BuyXGetY\Controllers\Console\BxgyController;

Route::get('/buy-x-get-y', [BxgyController::class, 'index'])->name('buy_x_get_y.index');
Route::post('/buy-x-get-y', [BxgyController::class, 'store'])->name('buy_x_get_y.store');
Route::delete('/buy-x-get-y/{id}', [BxgyController::class, 'destroy'])->name('buy_x_get_y.destroy');
