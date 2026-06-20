<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ImageGuard\Controllers\Console\ImageGuardController;

Route::get('/image-guard', [ImageGuardController::class, 'index'])->name('image_guard.index');
Route::post('/image-guard/preview', [ImageGuardController::class, 'preview'])->name('image_guard.preview');
Route::post('/image-guard/process', [ImageGuardController::class, 'processDir'])->name('image_guard.process');
