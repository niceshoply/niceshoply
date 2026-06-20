<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Booking\Controllers\Console\BookingController;

Route::get('/booking/services', [BookingController::class, 'services'])->name('booking.services');
Route::post('/booking/services', [BookingController::class, 'storeService'])->name('booking.services.store');
Route::delete('/booking/services/{id}', [BookingController::class, 'destroyService'])->name('booking.services.destroy');

Route::get('/booking/bookings', [BookingController::class, 'bookings'])->name('booking.bookings');
Route::put('/booking/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('booking.bookings.status');
