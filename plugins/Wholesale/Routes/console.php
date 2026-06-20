<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Wholesale\Controllers\Console\WholesaleController;

Route::get('/wholesale', [WholesaleController::class, 'index'])->name('wholesale.index');
Route::post('/wholesale', [WholesaleController::class, 'store'])->name('wholesale.store');
Route::delete('/wholesale/{id}', [WholesaleController::class, 'destroy'])->name('wholesale.destroy');
