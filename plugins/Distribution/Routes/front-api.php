<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Distribution\Controllers\Front\DistributionController;

Route::post('/distribution/become', [DistributionController::class, 'become'])->name('distribution.become');
Route::post('/distribution/bind', [DistributionController::class, 'bind'])->name('distribution.bind');
Route::get('/distribution/mine', [DistributionController::class, 'mine'])->name('distribution.mine');
Route::get('/distribution/commissions', [DistributionController::class, 'commissions'])->name('distribution.my_commissions');
