<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\GiftCard\Controllers\Console\GiftCardController;

Route::get('/gift-card', [GiftCardController::class, 'index'])->name('gift_card.index');
Route::post('/gift-card/generate', [GiftCardController::class, 'generate'])->name('gift_card.generate');
Route::get('/gift-card/batches/{batchId}/cards', [GiftCardController::class, 'cards'])->name('gift_card.cards');
Route::post('/gift-card/cards/{id}/toggle', [GiftCardController::class, 'toggleCard'])->name('gift_card.cards.toggle');
