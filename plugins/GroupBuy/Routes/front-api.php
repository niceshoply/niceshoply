<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\GroupBuy\Controllers\Front\GroupBuyController;

Route::get('/group_buy/groups/{id}', [GroupBuyController::class, 'group'])->name('group_buy.group');
Route::post('/group_buy/open', [GroupBuyController::class, 'open'])->name('group_buy.open');
Route::post('/group_buy/join', [GroupBuyController::class, 'join'])->name('group_buy.join');
