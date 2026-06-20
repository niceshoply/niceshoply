<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Subscription\Controllers\Front\SubscriptionController;

Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscription.index');
Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('subscription.store');
Route::post('/subscriptions/{id}/pause', [SubscriptionController::class, 'pause'])->name('subscription.pause');
Route::post('/subscriptions/{id}/resume', [SubscriptionController::class, 'resume'])->name('subscription.resume');
Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
