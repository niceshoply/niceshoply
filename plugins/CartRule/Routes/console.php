<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\CartRule\Controllers\Console\CartRuleController;

Route::get('/cart_rules', [CartRuleController::class, 'index'])->name('cart_rules.index');
Route::get('/cart_rules/create', [CartRuleController::class, 'create'])->name('cart_rules.create');
Route::post('/cart_rules', [CartRuleController::class, 'store'])->name('cart_rules.store');
Route::get('/cart_rules/{id}/edit', [CartRuleController::class, 'edit'])->name('cart_rules.edit');
Route::put('/cart_rules/{id}', [CartRuleController::class, 'update'])->name('cart_rules.update');
Route::delete('/cart_rules/{id}', [CartRuleController::class, 'destroy'])->name('cart_rules.destroy');
