<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\LuckyDraw\Controllers\Front\LuckyDrawController;

Route::get('/lucky-draw/info', [LuckyDrawController::class, 'info'])->name('lucky_draw.info');
Route::post('/lucky-draw/draw', [LuckyDrawController::class, 'draw'])->name('lucky_draw.draw');
