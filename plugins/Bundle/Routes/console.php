<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Bundle\Controllers\Console\BundleController;

Route::get('/bundle', [BundleController::class, 'index'])->name('bundle.index');
Route::post('/bundle', [BundleController::class, 'store'])->name('bundle.store');
Route::delete('/bundle/{id}', [BundleController::class, 'destroy'])->name('bundle.destroy');
