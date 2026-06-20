<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\PrintCenter\Controllers\Console\PrintCenterController;

Route::get('/print-center', [PrintCenterController::class, 'index'])->name('print_center.index');
Route::get('/print-center/print/{type}', [PrintCenterController::class, 'print'])->name('print_center.print');
