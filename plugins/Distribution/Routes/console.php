<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Distribution\Controllers\Console\DistributionController;

Route::get('/distribution/commissions', [DistributionController::class, 'commissions'])->name('distribution.commissions');
Route::get('/distribution/distributors', [DistributionController::class, 'distributors'])->name('distribution.distributors');
Route::post('/distribution/commissions/{id}/settle', [DistributionController::class, 'settle'])->name('distribution.settle');
