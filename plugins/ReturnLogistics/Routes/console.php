<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ReturnLogistics\Controllers\Console\ReturnLogisticsController;

Route::get('/return-logistics', [ReturnLogisticsController::class, 'index'])->name('return_logistics.index');
Route::post('/return-logistics/address', [ReturnLogisticsController::class, 'storeAddress'])->name('return_logistics.address.store');
Route::post('/return-logistics/shipment', [ReturnLogisticsController::class, 'createShipment'])->name('return_logistics.shipment.create');
Route::post('/return-logistics/{id}/received', [ReturnLogisticsController::class, 'markReceived'])->name('return_logistics.received');
