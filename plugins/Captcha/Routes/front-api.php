<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Captcha\Controllers\Front\CaptchaController;

Route::get('/captcha/config', [CaptchaController::class, 'config'])->name('captcha.config');
Route::post('/captcha/verify', [CaptchaController::class, 'verify'])->name('captcha.verify');
