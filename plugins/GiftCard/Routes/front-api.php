<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\GiftCard\Controllers\Front\GiftCardController;

Route::post('/gift-card/redeem', [GiftCardController::class, 'redeem'])->name('gift_card.redeem');
Route::get('/gift-card/my', [GiftCardController::class, 'myCards'])->name('gift_card.my');
