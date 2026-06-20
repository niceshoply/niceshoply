<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\VirtualGoods\Controllers\Console\VirtualGoodsController;

Route::get('/virtual-goods', [VirtualGoodsController::class, 'index'])->name('virtual_goods.index');
Route::post('/virtual-goods', [VirtualGoodsController::class, 'store'])->name('virtual_goods.store');
Route::delete('/virtual-goods/{id}', [VirtualGoodsController::class, 'destroy'])->name('virtual_goods.destroy');
Route::post('/virtual-goods/import', [VirtualGoodsController::class, 'importCards'])->name('virtual_goods.import');
Route::get('/virtual-goods/cards', [VirtualGoodsController::class, 'cards'])->name('virtual_goods.cards');
Route::get('/virtual-goods/deliveries', [VirtualGoodsController::class, 'deliveries'])->name('virtual_goods.deliveries');
