<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\SmsMarketing\Controllers\UnsubscribeController;

Route::get('/sms/unsubscribe', [UnsubscribeController::class, 'unsubscribe'])->name('sms_marketing.unsubscribe');
