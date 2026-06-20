<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\TranslatorAi\Controllers\Console\TranslatorController;

Route::get('/translator', [TranslatorController::class, 'index'])->name('translator.index');
Route::post('/translator/text', [TranslatorController::class, 'text'])->name('translator.text');
Route::post('/translator/lines', [TranslatorController::class, 'lines'])->name('translator.lines');
