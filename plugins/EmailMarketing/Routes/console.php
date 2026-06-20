<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\EmailMarketing\Controllers\Console\EmailMarketingController;

Route::get('/email-marketing/campaigns', [EmailMarketingController::class, 'campaigns'])->name('email_marketing.campaigns');
Route::post('/email-marketing/campaigns', [EmailMarketingController::class, 'storeCampaign'])->name('email_marketing.campaigns.store');
Route::post('/email-marketing/campaigns/{id}/send', [EmailMarketingController::class, 'send'])->name('email_marketing.campaigns.send');
Route::delete('/email-marketing/campaigns/{id}', [EmailMarketingController::class, 'destroyCampaign'])->name('email_marketing.campaigns.destroy');
Route::get('/email-marketing/subscribers', [EmailMarketingController::class, 'subscribers'])->name('email_marketing.subscribers');
