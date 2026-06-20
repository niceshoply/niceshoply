<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\LuckyDraw\Controllers\Console\LuckyDrawController;

Route::get('/lucky-draw/prizes', [LuckyDrawController::class, 'prizes'])->name('lucky_draw.prizes');
Route::post('/lucky-draw/prizes', [LuckyDrawController::class, 'store'])->name('lucky_draw.prizes.store');
Route::delete('/lucky-draw/prizes/{id}', [LuckyDrawController::class, 'destroy'])->name('lucky_draw.prizes.destroy');
Route::get('/lucky-draw/records', [LuckyDrawController::class, 'records'])->name('lucky_draw.records');
