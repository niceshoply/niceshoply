<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ReturnLogistics\Controllers\Front\ReturnController;

Route::get('/return-logistics/{aftersaleId}', [ReturnController::class, 'info'])->whereNumber('aftersaleId')->name('return_logistics.info');
Route::post('/return-logistics/{aftersaleId}/tracking', [ReturnController::class, 'submitTracking'])->whereNumber('aftersaleId')->name('return_logistics.tracking');
