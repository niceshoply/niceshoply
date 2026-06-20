<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\PopupNotice\Controllers\Console\NoticeController;

Route::get('/popup-notice', [NoticeController::class, 'index'])->name('popup_notice.index');
Route::get('/popup-notice/create', [NoticeController::class, 'create'])->name('popup_notice.create');
Route::post('/popup-notice', [NoticeController::class, 'store'])->name('popup_notice.store');
Route::get('/popup-notice/{id}/edit', [NoticeController::class, 'edit'])->name('popup_notice.edit');
Route::put('/popup-notice/{id}', [NoticeController::class, 'update'])->name('popup_notice.update');
Route::delete('/popup-notice/{id}', [NoticeController::class, 'destroy'])->name('popup_notice.destroy');
