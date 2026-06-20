<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ProductQa\Controllers\Front\ProductQAController;

Route::get('/product-qa', [ProductQAController::class, 'index'])->name('product_qa.list');
Route::post('/product-qa/ask', [ProductQAController::class, 'ask'])->name('product_qa.ask');
Route::post('/product-qa/answer', [ProductQAController::class, 'answer'])->name('product_qa.answer');
