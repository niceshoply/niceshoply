<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\AiMarketing\Controllers\Console\AiMarketingController;

Route::get('/ai-marketing', [AiMarketingController::class, 'index'])->name('ai_marketing.index');
Route::post('/ai-marketing/generate', [AiMarketingController::class, 'generate'])->name('ai_marketing.generate');
