<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Facades\Route;
use Plugin\Membership\Controllers\Console\MembershipLevelController;

Route::get('/membership_levels', [MembershipLevelController::class, 'index'])->name('membership_levels.index');
Route::get('/membership_levels/create', [MembershipLevelController::class, 'create'])->name('membership_levels.create');
Route::post('/membership_levels', [MembershipLevelController::class, 'store'])->name('membership_levels.store');
Route::get('/membership_levels/{id}/edit', [MembershipLevelController::class, 'edit'])->name('membership_levels.edit');
Route::put('/membership_levels/{id}', [MembershipLevelController::class, 'update'])->name('membership_levels.update');
Route::delete('/membership_levels/{id}', [MembershipLevelController::class, 'destroy'])->name('membership_levels.destroy');
