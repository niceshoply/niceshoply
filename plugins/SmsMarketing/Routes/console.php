<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\SmsMarketing\Controllers\Console\SmsMarketingController;

Route::get('/sms-marketing', [SmsMarketingController::class, 'index'])->name('sms_marketing.index');
Route::post('/sms-marketing', [SmsMarketingController::class, 'store'])->name('sms_marketing.store');
Route::post('/sms-marketing/{id}/send', [SmsMarketingController::class, 'send'])->name('sms_marketing.send');
Route::delete('/sms-marketing/{id}', [SmsMarketingController::class, 'destroy'])->name('sms_marketing.destroy');
