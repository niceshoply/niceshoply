<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\ProductQa\Controllers\Console\ProductQAController;

Route::get('/product-qa', [ProductQAController::class, 'index'])->name('product_qa.index');
Route::post('/product-qa/questions/{id}/audit', [ProductQAController::class, 'auditQuestion'])->name('product_qa.q.audit');
Route::post('/product-qa/questions/{id}/featured', [ProductQAController::class, 'toggleFeatured'])->name('product_qa.q.featured');
Route::post('/product-qa/questions/{id}/reply', [ProductQAController::class, 'merchantReply'])->name('product_qa.q.reply');
Route::post('/product-qa/answers/{id}/audit', [ProductQAController::class, 'auditAnswer'])->name('product_qa.a.audit');
