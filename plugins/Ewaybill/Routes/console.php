<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Ewaybill\Controllers\Console\EWaybillController;

Route::get('/ewaybill', [EWaybillController::class, 'index'])->name('ewaybill.index');
Route::post('/ewaybill/create', [EWaybillController::class, 'create'])->name('ewaybill.create');
