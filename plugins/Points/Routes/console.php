<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Points\Controllers\Console\PointController;

Route::get('/points', [PointController::class, 'index'])->name('points.index');
Route::post('/points/adjust', [PointController::class, 'adjust'])->name('points.adjust');
