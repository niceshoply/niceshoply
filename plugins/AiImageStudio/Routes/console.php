<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\AiImageStudio\Controllers\Console\AiImageStudioController;

Route::get('/ai-image-studio', [AiImageStudioController::class, 'index'])->name('ai_image_studio.index');
Route::post('/ai-image-studio/generate', [AiImageStudioController::class, 'generate'])->name('ai_image_studio.generate');
Route::delete('/ai-image-studio/{id}', [AiImageStudioController::class, 'destroy'])->name('ai_image_studio.destroy');
