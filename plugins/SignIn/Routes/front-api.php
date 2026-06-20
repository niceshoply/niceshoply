<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\SignIn\Controllers\Front\SignInController;

Route::get('/sign-in/status', [SignInController::class, 'status'])->name('sign_in.status');
Route::post('/sign-in', [SignInController::class, 'signIn'])->name('sign_in.do');
