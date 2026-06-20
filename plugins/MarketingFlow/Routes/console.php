<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\MarketingFlow\Controllers\Console\MarketingFlowController;

Route::get('/marketing-flow', [MarketingFlowController::class, 'index'])->name('marketing_flow.index');
Route::post('/marketing-flow', [MarketingFlowController::class, 'store'])->name('marketing_flow.store');
Route::delete('/marketing-flow/{id}', [MarketingFlowController::class, 'destroy'])->name('marketing_flow.destroy');
Route::post('/marketing-flow/run', [MarketingFlowController::class, 'run'])->name('marketing_flow.run');
