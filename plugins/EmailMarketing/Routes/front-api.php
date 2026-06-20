<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\EmailMarketing\Controllers\Front\SubscribeController;

Route::post('/email-marketing/subscribe', [SubscribeController::class, 'subscribe'])->name('email_marketing.subscribe');
