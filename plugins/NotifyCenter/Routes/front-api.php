<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\NotifyCenter\Controllers\Front\NotificationController;

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.list');
Route::get('/notifications/unread', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
Route::post('/notifications/read', [NotificationController::class, 'read'])->name('notifications.read');
Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read_all');
