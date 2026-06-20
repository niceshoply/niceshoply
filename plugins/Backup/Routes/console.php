<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Backup\Controllers\Console\BackupController;

Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
Route::post('/backup/create', [BackupController::class, 'create'])->name('backup.create');
Route::get('/backup/download', [BackupController::class, 'download'])->name('backup.download');
Route::post('/backup/destroy', [BackupController::class, 'destroy'])->name('backup.destroy');
