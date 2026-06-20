<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Bargain\Controllers\Front\BargainController;

Route::post('/bargain/start', [BargainController::class, 'start'])->name('bargain.start');
Route::post('/bargain/cut', [BargainController::class, 'cut'])->name('bargain.cut');
Route::get('/bargain/task/{id}', [BargainController::class, 'task'])->name('bargain.task');
Route::post('/bargain/apply', [BargainController::class, 'apply'])->name('bargain.apply');
